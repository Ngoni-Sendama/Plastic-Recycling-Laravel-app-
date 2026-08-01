<?php

namespace App\Services;

use App\Models\Material;
use App\Models\SyncConflict;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SyncConflictResolver
{
    /**
     * Keep the server record as authoritative and close the conflict.
     */
    public function keepServer(SyncConflict $conflict, User $resolver): void
    {
        $this->resolve($conflict, $resolver, 'keep_server', 'resolved');

        // Touch the real record so it is included in the next pull sync and the
        // mobile app can clear its local pending/conflict state.
        $this->touchRecord($conflict);
    }

    /**
     * Apply the full submitted mobile payload to the real server record.
     */
    public function acceptSubmitted(SyncConflict $conflict, User $resolver): void
    {
        $this->applyPayload($conflict, $resolver, $conflict->submitted_payload ?? [], 'accept_submitted');
    }

    /**
     * Apply only the selected fields from the submitted payload.
     *
     * @param  array<int, string>  $fields
     */
    public function mergeFields(SyncConflict $conflict, User $resolver, array $fields): void
    {
        if ($fields === []) {
            throw new RuntimeException('Select at least one field to merge.');
        }

        $submitted = $conflict->submitted_payload ?? [];

        $payload = array_intersect_key($submitted, array_flip($fields));

        $this->applyPayload($conflict, $resolver, $payload, 'manual_merge');
    }

    /**
     * Discard the submitted change without touching the real record.
     */
    public function discard(SyncConflict $conflict, User $resolver): void
    {
        $this->resolve($conflict, $resolver, 'discard_submitted', 'discarded');

        // Touch so the mobile app can reconcile on the next pull.
        $this->touchRecord($conflict);
    }

    /**
     * Apply a payload to the real record, recalculate derived fields, bump the
     * lock version, and mark the conflict resolved.
     *
     * @param  array<string, mixed>  $payload
     */
    private function applyPayload(SyncConflict $conflict, User $resolver, array $payload, string $resolution): void
    {
        $modelClass = SyncTableRegistry::model($conflict->table_name);
        $record = $modelClass::withTrashed()->find($conflict->record_id);

        if (! $record) {
            throw new RuntimeException('The referenced server record no longer exists.');
        }

        // Merge existing attributes so partial payloads recalculate correctly.
        $merged = array_merge($record->getAttributes(), $payload);

        $data = $this->validateAndPrepare($conflict->table_name, $merged, $record);
        unset($data['recorded_by_user_id']); // keep original attribution

        $record->fill($data);
        $record->lock_version = (int) $record->lock_version + 1;
        $record->save();

        $this->resolve($conflict, $resolver, $resolution, 'resolved');
    }

    /**
     * Validate and normalise a merged payload using the shared per-table rules,
     * resolving material codes and recalculating derived fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validateAndPrepare(string $table, array $data, Model $record): array
    {
        $validator = Validator::make($data, SyncTableRegistry::rulesFor($table, $record->id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if (SyncTableRegistry::isMaterialTable($table)) {
            if (! empty($data['material_code'])) {
                $materialId = Material::where('code', $data['material_code'])->value('id');

                if (! $materialId) {
                    throw new RuntimeException("Material code '{$data['material_code']}' does not exist.");
                }

                $data['material_id'] = (int) $materialId;
            }

            unset($data['material_code']);
        }

        $calculatorClass = SyncTableRegistry::calculator($table);

        if ($calculatorClass !== null) {
            $data = array_merge($data, $calculatorClass::calculate($data));
        }

        return $data;
    }

    private function resolve(SyncConflict $conflict, User $resolver, string $resolution, string $status): void
    {
        $conflict->update([
            'status' => $status,
            'resolution' => $resolution,
            'resolved_by_user_id' => $resolver->id,
            'resolved_at' => now(),
        ]);
    }

    private function touchRecord(SyncConflict $conflict): void
    {
        if (! $conflict->record_id) {
            return;
        }

        $modelClass = SyncTableRegistry::model($conflict->table_name);
        $record = $modelClass::withTrashed()->find($conflict->record_id);

        $record?->touch();
    }
}
