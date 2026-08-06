<?php

namespace App\Services;

use App\Http\Resources\BuyerResource;
use App\Http\Resources\CashRemittanceResource;
use App\Http\Resources\CrushingProductionResource;
use App\Http\Resources\DispatchResource;
use App\Http\Resources\ExpenseCategoryResource;
use App\Http\Resources\ExpenseResource;
use App\Http\Resources\MaterialIntakeResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\PalletizingProductionResource;
use App\Http\Resources\PalletizingReceiptResource;
use App\Http\Resources\PelletSaleResource;
use App\Models\Buyer;
use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use Illuminate\Validation\Rule;

class SyncTableRegistry
{
    /**
     * Table => [model class, API resource class, calculator class (nullable)].
     *
     * @return array<string, array{0: class-string, 1: class-string, 2: class-string|null}>
     */
    public static function tables(): array
    {
        return [
            'materials' => [Material::class, MaterialResource::class, null],
            'buyers' => [Buyer::class, BuyerResource::class, null],
            'material_intakes' => [MaterialIntake::class, MaterialIntakeResource::class, MaterialIntakeCalculator::class],
            'crushing_productions' => [CrushingProduction::class, CrushingProductionResource::class, CrushingProductionCalculator::class],
            'dispatches' => [Dispatch::class, DispatchResource::class, null],
            'palletizing_receipts' => [PalletizingReceipt::class, PalletizingReceiptResource::class, PalletizingReceiptCalculator::class],
            'palletizing_productions' => [PalletizingProduction::class, PalletizingProductionResource::class, PalletizingProductionCalculator::class],
            'pellet_sales' => [PelletSale::class, PelletSaleResource::class, PelletSaleCalculator::class],
            'cash_remittances' => [CashRemittance::class, CashRemittanceResource::class, CashRemittanceCalculator::class],
            'expense_categories' => [ExpenseCategory::class, ExpenseCategoryResource::class, null],
            'expenses' => [Expense::class, ExpenseResource::class, null],
        ];
    }

    /**
     * @return class-string
     */
    public static function model(string $table): string
    {
        return self::tables()[$table][0];
    }

    /**
     * @return class-string
     */
    public static function resource(string $table): string
    {
        return self::tables()[$table][1];
    }

    /**
     * @return class-string|null
     */
    public static function calculator(string $table): ?string
    {
        return self::tables()[$table][2];
    }

    /**
     * Tables that reference a material through material_code / material_id.
     *
     * @return array<int, string>
     */
    public static function materialTables(): array
    {
        return [
            'material_intakes',
            'crushing_productions',
            'dispatches',
            'palletizing_receipts',
        ];
    }

    public static function isMaterialTable(string $table): bool
    {
        return in_array($table, self::materialTables(), true);
    }

    /**
     * Validation rules per table, shared by the sync push path and the
     * conflict resolver so both validate identically.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(string $table, ?int $ignoreId = null): array
    {
        return match ($table) {
            'buyers' => [
                'buyer_name' => ['required', 'string', 'max:255'],
                'contact_number' => ['nullable', 'string', 'max:50'],
            ],
            'materials' => [
                'code' => ['required', 'string', 'max:255', Rule::unique('materials', 'code')->ignore($ignoreId)],
                'name' => ['required', 'string', 'max:255'],
            ],
            'material_intakes' => [
                'date' => ['required', 'date'],
                'buyer_id' => ['nullable', 'integer', 'exists:buyers,id'],
                'buyer_name' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'gross_weight_kg' => ['required', 'numeric', 'min:0'],
                'tare_weight_kg' => ['required', 'numeric', 'min:0'],
                'unit_price' => ['required', 'numeric', 'min:0'],
            ],
            'crushing_productions' => [
                'date' => ['required', 'date'],
                'material_intake_id' => ['nullable', 'integer', 'exists:material_intakes,id'],
                'grn_reference' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'input_weight_kg' => ['required', 'numeric', 'min:0'],
                'output_chips_kg' => ['required', 'numeric', 'min:0'],
            ],
            'dispatches' => [
                'date' => ['required', 'date'],
                'crushing_production_id' => ['nullable', 'integer', 'exists:crushing_productions,id'],
                'batch_reference' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'weight_dispatched_kg' => ['required', 'numeric', 'min:0'],
                'transported_by' => ['nullable', 'string', 'max:255'],
            ],
            'palletizing_receipts' => [
                'date' => ['required', 'date'],
                'dispatch_id' => ['nullable', 'integer', 'exists:dispatches,id'],
                'dispatch_reference' => ['nullable', 'string', 'max:255'],
                'material_id' => ['nullable', 'integer', 'exists:materials,id'],
                'material_code' => ['required_without:material_id', 'string', 'exists:materials,code'],
                'weight_received_kg' => ['required', 'numeric', 'min:0'],
                'rate_per_kg' => ['required', 'numeric', 'min:0'],
            ],
            'palletizing_productions' => [
                'date' => ['required', 'date'],
                'palletizing_receipt_id' => ['nullable', 'integer', 'exists:palletizing_receipts,id'],
                'grn_reference' => ['nullable', 'string', 'max:255'],
                'chips_input_kg' => ['required', 'numeric', 'min:0'],
                'pellets_output_kg' => ['required', 'numeric', 'min:0'],
            ],
            'pellet_sales' => [
                'date' => ['required', 'date'],
                'customer_name' => ['required', 'string', 'max:255'],
                'kg_sold' => ['required', 'numeric', 'min:0'],
                'unit_price' => ['required', 'numeric', 'min:0'],
            ],
            'cash_remittances' => [
                'date' => ['required', 'date'],
                'period_covered' => ['nullable', 'string', 'max:255'],
                'chips_delivered_kg' => ['required', 'numeric', 'min:0'],
                'recovery_price_per_kg' => ['required', 'numeric', 'min:0'],
                'sales_revenue' => ['required', 'numeric', 'min:0'],
                'cash_remitted' => ['required', 'numeric', 'min:0'],
            ],
            'expense_categories' => [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
            ],
            'expenses' => [
                'date' => ['required', 'date'],
                'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
                'description' => ['nullable', 'string', 'max:1000'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'payment_method' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }
}
