<?php

namespace App\Services;

use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ReportSummaryService
{
    /**
     * Material flow through the pipeline with a per-material breakdown.
     *
     * @return array<string, mixed>
     */
    public function stockSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $summary = app(DashboardSummaryService::class)->summary($from, $to);

        return [
            'period' => $this->period($from, $to),
            'totals' => [
                'material_purchased_kg' => (float) $summary['material_purchased_kg'],
                'chips_produced_kg' => (float) $summary['chips_produced_kg'],
                'chips_dispatched_kg' => (float) $summary['chips_dispatched_kg'],
                'chips_on_hand_kg' => (float) $summary['chips_on_hand_kg'],
                'chips_received_kg' => (float) $summary['chips_received_kg'],
                'receiving_variance_kg' => (float) $summary['receiving_variance_kg'],
                'pellets_produced_kg' => (float) $summary['pellets_produced_kg'],
                'pellets_sold_kg' => (float) $summary['pellets_sold_kg'],
                'finished_stock_kg' => (float) $summary['finished_stock_kg'],
            ],
            'per_material' => $this->stockByMaterial($from, $to),
        ];
    }

    /**
     * Crushing and palletizing performance with a per-material and monthly view.
     *
     * @return array<string, mixed>
     */
    public function productionSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $crushingInput = $this->scopedSum(CrushingProduction::query(), 'input_weight_kg', $from, $to);
        $crushingOutput = $this->scopedSum(CrushingProduction::query(), 'output_chips_kg', $from, $to);

        $palletizingInput = $this->scopedSum(PalletizingProduction::query(), 'chips_input_kg', $from, $to);
        $palletizingOutput = $this->scopedSum(PalletizingProduction::query(), 'pellets_output_kg', $from, $to);

        return [
            'period' => $this->period($from, $to),
            'crushing' => [
                'input_kg' => $crushingInput,
                'output_kg' => $crushingOutput,
                'loss_kg' => round($crushingInput - $crushingOutput, 3),
                'loss_percentage' => $this->lossPercentage($crushingInput, $crushingOutput),
            ],
            'palletizing' => [
                'input_kg' => $palletizingInput,
                'output_kg' => $palletizingOutput,
                'loss_kg' => round($palletizingInput - $palletizingOutput, 3),
                'loss_percentage' => $this->lossPercentage($palletizingInput, $palletizingOutput),
            ],
            'per_material' => $this->productionByMaterial($from, $to),
            'monthly' => $this->productionMonthly($from, $to),
        ];
    }

    /**
     * Pellet sales totals with per-customer and monthly views.
     *
     * @return array<string, mixed>
     */
    public function salesSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $kgSold = $this->scopedSum(PelletSale::query(), 'kg_sold', $from, $to);
        $revenue = $this->scopedSum(PelletSale::query(), 'amount_received', $from, $to);

        return [
            'period' => $this->period($from, $to),
            'totals' => [
                'kg_sold' => $kgSold,
                'revenue' => $revenue,
                'average_price_per_kg' => $kgSold > 0 ? round($revenue / $kgSold, 2) : 0.0,
                'transactions' => $this->scopedCount(PelletSale::query(), $from, $to),
            ],
            'per_customer' => $this->salesByCustomer($from, $to),
            'monthly' => $this->salesMonthly($from, $to),
        ];
    }

    /**
     * Cash remittances versus revenue with a per-voucher view.
     *
     * @return array<string, mixed>
     */
    public function cashReconciliation(?Carbon $from = null, ?Carbon $to = null): array
    {
        $summary = app(DashboardSummaryService::class)->summary($from, $to);

        return [
            'period' => $this->period($from, $to),
            'totals' => [
                'sales_revenue' => (float) $summary['sales_revenue'],
                'cash_remitted' => (float) $summary['cash_remitted'],
                'balance_retained' => (float) $summary['balance_retained'],
                'cash_collection_gap' => (float) $summary['cash_collection_gap'],
                'payable_to_crushing' => (float) $summary['payable_to_crushing'],
                'outstanding_to_crushing' => (float) $summary['outstanding_to_crushing'],
                'reconciliation_status' => $summary['reconciliation_status'],
            ],
            'remittances' => $this->remittances($from, $to),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function period(?Carbon $from, ?Carbon $to): array
    {
        return [
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stockByMaterial(?Carbon $from, ?Carbon $to): array
    {
        $purchased = $this->materialSum(MaterialIntake::query(), 'net_weight_kg', $from, $to);
        $produced = $this->materialSum(CrushingProduction::query(), 'output_chips_kg', $from, $to);
        $dispatched = $this->materialSum(Dispatch::query(), 'weight_dispatched_kg', $from, $to);
        $received = $this->materialSum(PalletizingReceipt::query(), 'weight_received_kg', $from, $to);

        return Material::query()->orderBy('code')
            ->get()
            ->map(function (Material $material) use ($purchased, $produced, $dispatched, $received): array {
                $producedKg = $produced[$material->id] ?? 0.0;
                $dispatchedKg = $dispatched[$material->id] ?? 0.0;

                return [
                    'material_code' => $material->code,
                    'material_name' => $material->name,
                    'purchased_kg' => $purchased[$material->id] ?? 0.0,
                    'produced_kg' => $producedKg,
                    'dispatched_kg' => $dispatchedKg,
                    'received_kg' => $received[$material->id] ?? 0.0,
                    'on_hand_kg' => round($producedKg - $dispatchedKg, 3),
                ];
            })
            ->filter(fn (array $row): bool => collect($row)->except(['material_code', 'material_name'])->sum() !== 0.0)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productionByMaterial(?Carbon $from, ?Carbon $to): array
    {
        return CrushingProduction::query()
            ->selectRaw('material_id, SUM(input_weight_kg) as input_kg, SUM(output_chips_kg) as output_kg')
            ->with('material')
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->orderBy('material_id')
            ->get()
            ->map(function (CrushingProduction $row): array {
                $inputKg = (float) $row->input_kg;
                $outputKg = (float) $row->output_kg;

                return [
                    'material_code' => $row->material?->code,
                    'material_name' => $row->material?->name,
                    'input_kg' => $inputKg,
                    'output_kg' => $outputKg,
                    'loss_kg' => round($inputKg - $outputKg, 3),
                    'loss_percentage' => $this->lossPercentage($inputKg, $outputKg),
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productionMonthly(?Carbon $from, ?Carbon $to): array
    {
        $crushing = $this->monthlySums(CrushingProduction::query(), 'output_chips_kg', $from, $to);
        $palletizing = $this->monthlySums(PalletizingProduction::query(), 'pellets_output_kg', $from, $to);
        $periods = collect(array_keys($crushing + $palletizing))->sort()->values();

        return $periods->map(fn (string $period): array => [
            'period' => $period,
            'crushing_output_kg' => $crushing[$period] ?? 0.0,
            'palletizing_output_kg' => $palletizing[$period] ?? 0.0,
        ])->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function salesByCustomer(?Carbon $from, ?Carbon $to): array
    {
        return PelletSale::query()
            ->selectRaw('customer_name, SUM(kg_sold) as kg_sold, SUM(amount_received) as revenue, COUNT(*) as transactions')
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->groupBy('customer_name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn (PelletSale $row): array => [
                'customer_name' => $row->customer_name,
                'kg_sold' => (float) $row->kg_sold,
                'revenue' => (float) $row->revenue,
                'transactions' => (int) $row->transactions,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function salesMonthly(?Carbon $from, ?Carbon $to): array
    {
        $monthly = $this->monthlySums(PelletSale::query(), 'amount_received', $from, $to);
        $kg = $this->monthlySums(PelletSale::query(), 'kg_sold', $from, $to);

        return collect(array_keys($monthly + $kg))->sort()->values()
            ->map(fn (string $period): array => [
                'period' => $period,
                'kg_sold' => $kg[$period] ?? 0.0,
                'revenue' => $monthly[$period] ?? 0.0,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function remittances(?Carbon $from, ?Carbon $to): array
    {
        return CashRemittance::query()
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->latest('date')
            ->get()
            ->map(fn (CashRemittance $row): array => [
                'date' => $row->date?->toDateString(),
                'voucher_number' => $row->voucher_number,
                'period_covered' => $row->period_covered,
                'chips_delivered_kg' => (float) $row->chips_delivered_kg,
                'recovery_price_per_kg' => (float) $row->recovery_price_per_kg,
                'sales_revenue' => (float) $row->sales_revenue,
                'cash_remitted' => (float) $row->cash_remitted,
                'max_remittance_due' => (float) $row->max_remittance_due,
                'balance_retained' => (float) $row->balance_retained,
            ])
            ->all();
    }

    /**
     * @param  Builder<MaterialIntake|CrushingProduction|Dispatch|PalletizingReceipt>  $query
     * @return array<int, float>
     */
    private function materialSum(Builder $query, string $column, ?Carbon $from, ?Carbon $to): array
    {
        return $query->clone()
            ->selectRaw("material_id, SUM({$column}) as total")
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->pluck('total', 'material_id')
            ->map(fn ($value): float => (float) $value)
            ->all();
    }

    /**
     * @param  Builder<CrushingProduction|PalletizingProduction|PelletSale>  $query
     * @return array<string, float>
     */
    private function monthlySums(Builder $query, string $column, ?Carbon $from, ?Carbon $to): array
    {
        $rows = $query->clone()
            ->select(['date', $column])
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->get();

        return $rows
            ->groupBy(fn ($row): string => $row->date?->format('Y-m') ?? 'unknown')
            ->map(fn ($group): float => round((float) $group->sum($column), 3))
            ->all();
    }

    /**
     * @param  Builder<CrushingProduction|PalletizingProduction|PelletSale>  $query
     */
    private function scopedSum(Builder $query, string $column, ?Carbon $from, ?Carbon $to): float
    {
        return (float) $query->clone()
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->sum($column);
    }

    /**
     * @param  Builder<PelletSale>  $query
     */
    private function scopedCount(Builder $query, ?Carbon $from, ?Carbon $to): int
    {
        return $query->clone()
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->count();
    }

    private function lossPercentage(float $inputKg, float $outputKg): float
    {
        if ($inputKg <= 0) {
            return 0.0;
        }

        return round(($inputKg - $outputKg) / $inputKg, 4);
    }
}
