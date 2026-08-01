<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SyncPushRequest;
use App\Http\Resources\CashRemittanceResource;
use App\Http\Resources\CrushingProductionResource;
use App\Http\Resources\DispatchResource;
use App\Http\Resources\MaterialIntakeResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\PalletizingProductionResource;
use App\Http\Resources\PalletizingReceiptResource;
use App\Http\Resources\PelletSaleResource;
use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use App\Models\SyncConflict;
use App\Models\User;
use App\Services\CashRemittanceCalculator;
use App\Services\CrushingProductionCalculator;
use App\Services\MaterialIntakeCalculator;
use App\Services\PalletizingProductionCalculator;
use App\Services\PalletizingReceiptCalculator;
use App\Services\PelletSaleCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SyncController extends ApiController
{
    /**
     * Table => [model class, resource class, calculator class (nullable)].
     *
     * @var array<string, array{0: class-string, 1: class-string, 2: class-string|null}>
     */
    private array $tables = [
        'materials' => [Material::class, MaterialResource::class, null],
        'material_intakes' => [MaterialIntake::class, MaterialIntakeResource::class, MaterialIntakeCalculator::class],
        'crushing_productions' => [CrushingProduction::class, CrushingProductionResource::class, CrushingProductionCalculator::class],
        'dispatches' => [Dispatch::class, DispatchResource::class, null],
        'palletizing_receipts' => [PalletizingReceipt::class, PalletizingReceiptResource::class, PalletizingReceiptCalculator::class],
        'palletizing_productions' => [PalletizingProduction::class, PalletizingProductionResource::class, PalletizingProductionCalculator::class],
        'pellet_sales' => [PelletSale::class, PelletSaleResource::class, PelletSaleCalculator::class],
        'cash_remittances' => [CashRemittance::class, CashRemittanceResource::class, CashRemittanceCalculator::class],
    ];

    /**
     * Tables that reference a material through material_code / material_id.
     *
     * @var array<int, string>
     */
    private array $materialTables = [
        'material_intakes',
        'crushing_productions',
        'dispatches',
        'palletizing_receipts',
    ];

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

        foreach ($this->tables as $table => [$modelClass, $resourceClass]) {
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
            if (! isset($this->tables[$table])) {
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
        [$modelClass, $resourceClass, $calculatorClass] = $this->tables[$table];

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
        $validator = Validator::make($data, $this->rulesFor($table, $ignoreId));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        if (in_array($table, $this->materialTables, true)) {
            $data['material_id'] = $this->resolveMaterialId($data);
            unset($data['material_code']);
        }

        [, , $calculatorClass] = $this->tables[$table];

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

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rulesFor(string $table, ?int $ignoreId = null): array
    {
        return match ($table) {
            'materials' => [
                'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->ignore($ignoreId)],
                'name' => ['required', 'string', 'max:255'],
            ],
            'material_intakes' => [
                'date' => ['required', 'date'],
                'grn_number' => ['required', 'string', 'max:255'],
                'buyer_name' => ['required', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'gross_weight_kg' => ['required', 'numeric', 'min:0'],
                'tare_weight_kg' => ['required', 'numeric', 'min:0'],
                'unit_price' => ['required', 'numeric', 'min:0'],
            ],
            'crushing_productions' => [
                'date' => ['required', 'date'],
                'batch_number' => ['required', 'string', 'max:255'],
                'material_intake_id' => ['nullable', 'integer', 'exists:material_intakes,id'],
                'grn_reference' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'input_weight_kg' => ['required', 'numeric', 'min:0'],
                'output_chips_kg' => ['required', 'numeric', 'min:0'],
            ],
            'dispatches' => [
                'date' => ['required', 'date'],
                'dispatch_note_number' => ['required', 'string', 'max:255'],
                'crushing_production_id' => ['nullable', 'integer', 'exists:crushing_productions,id'],
                'batch_reference' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'weight_dispatched_kg' => ['required', 'numeric', 'min:0'],
                'transported_by' => ['nullable', 'string', 'max:255'],
            ],
            'palletizing_receipts' => [
                'date' => ['required', 'date'],
                'grn_number' => ['required', 'string', 'max:255'],
                'dispatch_id' => ['nullable', 'integer', 'exists:dispatches,id'],
                'dispatch_reference' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'weight_received_kg' => ['required', 'numeric', 'min:0'],
                'rate_per_kg' => ['required', 'numeric', 'min:0'],
            ],
            'palletizing_productions' => [
                'date' => ['required', 'date'],
                'batch_number' => ['required', 'string', 'max:255'],
                'palletizing_receipt_id' => ['nullable', 'integer', 'exists:palletizing_receipts,id'],
                'grn_reference' => ['nullable', 'string', 'max:255'],
                'chips_input_kg' => ['required', 'numeric', 'min:0'],
                'pellets_output_kg' => ['required', 'numeric', 'min:0'],
            ],
            'pellet_sales' => [
                'date' => ['required', 'date'],
                'receipt_number' => ['required', 'string', 'max:255'],
                'customer_name' => ['required', 'string', 'max:255'],
                'kg_sold' => ['required', 'numeric', 'min:0'],
                'unit_price' => ['required', 'numeric', 'min:0'],
            ],
            'cash_remittances' => [
                'date' => ['required', 'date'],
                'voucher_number' => ['required', 'string', 'max:255'],
                'period_covered' => ['nullable', 'string', 'max:255'],
                'chips_delivered_kg' => ['required', 'numeric', 'min:0'],
                'recovery_price_per_kg' => ['required', 'numeric', 'min:0'],
                'sales_revenue' => ['required', 'numeric', 'min:0'],
                'cash_remitted' => ['required', 'numeric', 'min:0'],
            ],
            default => [],
        };
    }
}
