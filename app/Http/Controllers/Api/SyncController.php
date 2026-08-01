<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SyncPushRequest;
use App\Models\SyncConflict;
use App\Models\User;
use App\Services\SyncTableRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class SyncController extends ApiController
{
    /**
     * Pull all records changed since the given timestamp, including soft-deleted records.
     */
    public function pull(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => ['nullable', 'date'],
        ]);

        $since = ! empty($validated['since']) ? Carbon::parse($validated['since']) : null;

        $changes = [];
        $deleted = [];

        foreach (SyncTableRegistry::tables() as $table => [$modelClass, $resourceClass]) {
            $rows = $modelClass::query()
                ->withTrashed()
                ->when($since, fn ($query) => $query->where('updated_at', '>', $since))
                ->get();

            $changes[$table] = $rows->whereNull('deleted_at')
                ->map(fn ($row) => new $resourceClass($row))
                ->values();

            $deleted[$table] = $rows->whereNotNull('deleted_at')
                ->map(fn ($row) => new $resourceClass($row))
                ->values();
        }

        return response()->json([
            'server_time' => now()->toISOString(),
            'changes' => $changes,
            'deleted' => $deleted,
        ]);
    }

    /**
     * Accept offline-created and offline-updated records using optimistic locking.
     */
    public function push(SyncPushRequest $request): JsonResponse
    {
        $user = $request->user();
        $accepted = [];
        $conflicts = [];
        $rejected = [];

        foreach ($request->validated('changes', []) as $table => $changes) {
            if (! isset(SyncTableRegistry::tables()[$table])) {
                continue;
            }

            foreach ($changes as $change) {
                try {
                    $result = $this->syncChange($table, $change, $user);

                    match ($result['status']) {
                        'accepted' => $accepted[] = $result,
                        'conflict' => $conflicts[] = $result,
                        default => $rejected[] = $result,
                    };
                } catch (ValidationException $e) {
                    $rejected[] = [
                        'table' => $table,
                        'local_id' => $change['local_id'] ?? null,
                        'errors' => $e->errors(),
                    ];
                } catch (Throwable $e) {
                    $rejected[] = [
                        'table' => $table,
                        'local_id' => $change['local_id'] ?? null,
                        'errors' => [$e->getMessage()],
                    ];
                }
            }
        }

        return response()->json([
            'accepted' => $accepted,
            'conflicts' => $conflicts,
            'rejected' => $rejected,
        ]);
    }

    /**
     * @param  array<string, mixed>  $change
     * @return array<string, mixed>
     */
    private function syncChange(string $table, array $change, User $user): array
    {
        [$modelClass] = SyncTableRegistry::tables()[$table];

        $localId = $change['local_id'] ?? null;
        $serverId = $change['server_id'] ?? null;
        $submittedVersion = isset($change['server_lock_version']) ? (int) $change['server_lock_version'] : null;
        $deleted = (bool) ($change['deleted'] ?? false);
        $submittedData = $change['data'] ?? [];

        $base = ['table' => $table, 'local_id' => $localId];

        // Create a brand new record.
        if ($serverId === null) {
            $data = $this->prepareData($table, $submittedData, $user);

            if ($deleted) {
                return [...$base, 'status' => 'accepted', 'server_id' => null, 'deleted' => true];
            }

            $record = $modelClass::create($data);

            return [...$base, 'status' => 'accepted', 'server_id' => $record->id, 'lock_version' => (int) $record->lock_version];
        }

        // Find the existing server record (including trashed ones).
        $record = $modelClass::withTrashed()->find($serverId);

        if (! $record) {
            return [...$base, 'status' => 'rejected', 'errors' => ['Server record not found.']];
        }

        // Optimistic locking: the mobile must have synced from the current version.
        if ($submittedVersion !== (int) $record->lock_version) {
            $conflict = SyncConflict::create([
                'table_name' => $table,
                'record_id' => $record->id,
                'local_id' => $localId,
                'submitted_by_user_id' => $user->id,
                'server_version' => (int) $record->lock_version,
                'submitted_version' => $submittedVersion,
                'server_payload' => $record->toArray(),
                'submitted_payload' => $submittedData,
                'changed_fields' => array_keys($submittedData),
                'status' => 'pending',
            ]);

            return [...$base, 'status' => 'conflict', 'server_id' => $record->id, 'conflict_id' => $conflict->id, 'server_version' => (int) $record->lock_version];
        }

        // Soft delete requested.
        if ($deleted) {
            $record->lock_version = (int) $record->lock_version + 1;
            $record->save();
            $record->delete();

            return [...$base, 'status' => 'accepted', 'server_id' => $record->id, 'lock_version' => (int) $record->lock_version, 'deleted' => true];
        }

        // Merge existing attributes so partial payloads recalculate correctly.
        $merged = array_merge($record->getAttributes(), $submittedData);
        $data = $this->prepareData($table, $merged, $user, $record->id);
        unset($data['recorded_by_user_id']); // keep original attribution

        $record->fill($data);
        $record->lock_version = (int) $record->lock_version + 1;
        $record->save();

        return [...$base, 'status' => 'accepted', 'server_id' => $record->id, 'lock_version' => (int) $record->lock_version];
    }

    /**
     * Validate and normalise a change payload for a table.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $ignoreId  record id to ignore for unique rules (sync updates)
     * @return array<string, mixed>
     */
    private function prepareData(string $table, array $data, User $user, ?int $ignoreId = null): array
    {
        $validator = Validator::make($data, SyncTableRegistry::rulesFor($table, $ignoreId));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if (SyncTableRegistry::isMaterialTable($table)) {
            $data['material_id'] = $this->resolveMaterialId($data);
            unset($data['material_code']);
        }

        $calculatorClass = SyncTableRegistry::calculator($table);

        if ($calculatorClass !== null) {
            $data = array_merge($data, $calculatorClass::calculate($data));
        }

        // The reference tables have no recorder column; attribution only applies
        // to operational records.
        if ($table !== 'materials') {
            $data['recorded_by_user_id'] = $user->id;
        }

        return $data;
    }
}
