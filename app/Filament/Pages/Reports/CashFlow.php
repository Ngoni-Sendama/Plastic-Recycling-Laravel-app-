<?php

namespace App\Filament\Pages\Reports;

use App\Services\CashFlowReportService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Support\Icons\Heroicon;

class CashFlow extends BaseReportPage
{
    use HasPageShield;

    protected string $view = 'filament.pages.reports.cash-flow';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Cash Flow';

    protected static ?string $title = 'Cash Flow';

    protected static ?int $navigationSort = 5;

    protected function getViewData(): array
    {
        return [
            'report' => app(CashFlowReportService::class)->report($this->from(), $this->to()),
        ];
    }
}
