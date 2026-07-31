<?php

namespace App\Filament\Widgets;

use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected static bool $isLazy = false;

    protected ?string $heading = 'Operations Snapshot';

    protected ?string $description = 'Current stock, production, sales, and cash position.';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $materialPurchasedKg = (float) MaterialIntake::sum('net_weight_kg');
        $chipsProducedKg = (float) CrushingProduction::sum('output_chips_kg');
        $chipsDispatchedKg = (float) Dispatch::sum('weight_dispatched_kg');
        $chipsReceivedKg = (float) PalletizingReceipt::sum('weight_received_kg');
        $pelletsProducedKg = (float) PalletizingProduction::sum('pellets_output_kg');
        $pelletsSoldKg = (float) PelletSale::sum('kg_sold');
        $salesRevenue = (float) PelletSale::sum('amount_received');
        $cashRemitted = (float) CashRemittance::sum('cash_remitted');
        $balanceRetained = (float) CashRemittance::sum('balance_retained');

        $chipsOnHandKg = $chipsProducedKg - $chipsDispatchedKg;
        $receivingVarianceKg = $chipsDispatchedKg - $chipsReceivedKg;
        $finishedStockKg = $pelletsProducedKg - $pelletsSoldKg;
        $cashCollectionGap = $salesRevenue - $cashRemitted;

        return [
            Stat::make('Material purchased', $this->formatKg($materialPurchasedKg))
                ->description($materialPurchasedKg > 0 ? 'Raw material intake recorded' : 'Start by recording material intake')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Chips on hand', $this->formatKg($chipsOnHandKg))
                ->description($chipsOnHandKg >= 0 ? 'Produced chips less dispatches' : 'Dispatches exceed chips produced')
                ->descriptionIcon($chipsOnHandKg >= 0 ? 'heroicon-m-scale' : 'heroicon-m-exclamation-triangle')
                ->color($chipsOnHandKg >= 0 ? 'info' : 'danger'),

            Stat::make('Receiving variance', $this->formatKg($receivingVarianceKg))
                ->description(abs($receivingVarianceKg) <= 5 ? 'Dispatch and receipt are closely aligned' : 'Review dispatch versus receipt')
                ->descriptionIcon(abs($receivingVarianceKg) <= 5 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color(abs($receivingVarianceKg) <= 5 ? 'success' : 'warning'),

            Stat::make('Finished stock', $this->formatKg($finishedStockKg))
                ->description($finishedStockKg >= 0 ? 'Pellets produced less sales' : 'Sales exceed pellet production')
                ->descriptionIcon($finishedStockKg >= 0 ? 'heroicon-m-cube' : 'heroicon-m-exclamation-triangle')
                ->color($finishedStockKg >= 0 ? 'success' : 'danger'),

            Stat::make('Sales revenue', $this->formatMoney($salesRevenue))
                ->description($pelletsSoldKg > 0 ? $this->formatKg($pelletsSoldKg).' sold' : 'No pellet sales recorded')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Cash gap', $this->formatMoney($cashCollectionGap))
                ->description('Balance retained: '.$this->formatMoney($balanceRetained))
                ->descriptionIcon($cashCollectionGap <= $balanceRetained ? 'heroicon-m-check-circle' : 'heroicon-m-banknotes')
                ->color($cashCollectionGap <= $balanceRetained ? 'success' : 'warning'),
        ];
    }

    private function formatKg(float $value): string
    {
        return Number::format($value, precision: 1).' kg';
    }

    private function formatMoney(float $value): string
    {
        return '$'.Number::format($value, precision: 2);
    }
}
