<?php

namespace App\Filament\Pages\Reports;

use App\Services\ReportSummaryService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Support\Icons\Heroicon;

class SalesSummary extends BaseReportPage
{
    use HasPageShield;
    protected string $view = 'filament.pages.reports.sales-summary';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Sales Summary';

    protected static ?string $title = 'Sales Summary';

    protected static ?int $navigationSort = 3;

    protected function getViewData(): array
    {
        return [
            'report' => app(ReportSummaryService::class)->salesSummary($this->from(), $this->to()),
        ];
    }
}
