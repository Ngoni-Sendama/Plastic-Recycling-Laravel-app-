<?php

namespace App\Services;

use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Expense;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardSummaryService
{
    /**
     * Compute the operations snapshot used by the dashboard widgets.
     *
     * All derived metrics are computed server-side from stored column values so
     * the dashboard, reports, and API always agree.
     *
     * @return array<string, float|string>
     */
    public function summary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $materialPurchasedKg = $this->scopedSum(MaterialIntake::query(), 'net_weight_kg', $from, $to);
        $chipsProducedKg = $this->scopedSum(CrushingProduction::query(), 'output_chips_kg', $from, $to);
        $chipsDispatchedKg = $this->scopedSum(Dispatch::query(), 'weight_dispatched_kg', $from, $to);
        $chipsReceivedKg = $this->scopedSum(PalletizingReceipt::query(), 'weight_received_kg', $from, $to);
        $payableToCrushing = $this->scopedSum(PalletizingReceipt::query(), 'amount_payable', $from, $to);
        $pelletsProducedKg = $this->scopedSum(PalletizingProduction::query(), 'pellets_output_kg', $from, $to);
        $pelletsSoldKg = $this->scopedSum(PelletSale::query(), 'kg_sold', $from, $to);
        $salesRevenue = $this->scopedSum(PelletSale::query(), 'amount_received', $from, $to);
        $cashRemitted = $this->scopedSum(CashRemittance::query(), 'cash_remitted', $from, $to);
        $balanceRetained = $this->scopedSum(CashRemittance::query(), 'balance_retained', $from, $to);
        $totalExpenses = $this->scopedSum(Expense::query(), 'amount', $from, $to);

        $chipsOnHandKg = $chipsProducedKg - $chipsDispatchedKg;
        $receivingVarianceKg = $chipsDispatchedKg - $chipsReceivedKg;
        $finishedStockKg = $pelletsProducedKg - $pelletsSoldKg;
        $cashCollectionGap = $salesRevenue - $cashRemitted;
        $outstandingToCrushing = $payableToCrushing - $cashRemitted;
        $closingBalance = $salesRevenue - $cashRemitted - $totalExpenses;

        return [
            'material_purchased_kg' => $materialPurchasedKg,
            'chips_produced_kg' => $chipsProducedKg,
            'crushing_loss_percentage' => $this->lossPercentage(CrushingProduction::query(), 'input_weight_kg', 'loss_kg', $from, $to),
            'chips_dispatched_kg' => $chipsDispatchedKg,
            'chips_on_hand_kg' => $chipsOnHandKg,
            'chips_received_kg' => $chipsReceivedKg,
            'receiving_variance_kg' => $receivingVarianceKg,
            'payable_to_crushing' => $payableToCrushing,
            'pellets_produced_kg' => $pelletsProducedKg,
            'palletizing_loss_percentage' => $this->lossPercentage(PalletizingProduction::query(), 'chips_input_kg', 'loss_kg', $from, $to),
            'pellets_sold_kg' => $pelletsSoldKg,
            'finished_stock_kg' => $finishedStockKg,
            'sales_revenue' => $salesRevenue,
            'cash_remitted' => $cashRemitted,
            'balance_retained' => $balanceRetained,
            'cash_collection_gap' => $cashCollectionGap,
            'outstanding_to_crushing' => $outstandingToCrushing,
            'total_expenses' => $totalExpenses,
            'closing_balance' => $closingBalance,
            'reconciliation_status' => $cashCollectionGap <= $balanceRetained ? 'balanced' : 'shortfall',
        ];
    }

    /**
     * @param  Builder<CrushingProduction|PalletizingProduction>  $query
     */
    private function lossPercentage(Builder $query, string $inputColumn, string $lossColumn, ?Carbon $from, ?Carbon $to): float
    {
        $inputKg = $this->scopedSum($query, $inputColumn, $from, $to);

        if ($inputKg <= 0) {
            return 0.0;
        }

        return round($this->scopedSum($query, $lossColumn, $from, $to) / $inputKg, 4);
    }

    /**
     * @param  Builder<MaterialIntake|CrushingProduction|Dispatch|PalletizingReceipt|PalletizingProduction|PelletSale|CashRemittance>  $query
     */
    private function scopedSum(Builder $query, string $column, ?Carbon $from, ?Carbon $to): float
    {
        return (float) $query->clone()
            ->when($from, fn (Builder $query, Carbon $from): Builder => $query->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $query, Carbon $to): Builder => $query->whereDate('date', '<=', $to))
            ->sum($column);
    }
}
