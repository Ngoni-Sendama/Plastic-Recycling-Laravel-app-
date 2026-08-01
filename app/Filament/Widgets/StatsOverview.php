<?php

namespace App\Filament\Widgets;

use App\Services\DashboardSummaryService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    use HasWidgetShield;
    
    protected ?string $pollingInterval = '30s';

    protected static bool $isLazy = false;

    protected ?string $heading = 'Operations Snapshot';

    protected ?string $description = 'Current stock, production, sales, and cash position.';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $summary = app(DashboardSummaryService::class)->summary();

        $materialPurchasedKg = (float) $summary['material_purchased_kg'];
        $chipsOnHandKg = (float) $summary['chips_on_hand_kg'];
        $receivingVarianceKg = (float) $summary['receiving_variance_kg'];
        $finishedStockKg = (float) $summary['finished_stock_kg'];
        $salesRevenue = (float) $summary['sales_revenue'];
        $cashCollectionGap = (float) $summary['cash_collection_gap'];
        $balanceRetained = (float) $summary['balance_retained'];
        $pelletsSoldKg = (float) $summary['pellets_sold_kg'];

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
