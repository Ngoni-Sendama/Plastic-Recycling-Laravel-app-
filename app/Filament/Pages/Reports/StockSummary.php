<?php

namespace App\Filament\Pages\Reports;

use App\Services\ReportSummaryService;
use Filament\Support\Icons\Heroicon;

class StockSummary extends BaseReportPage
{
    protected string $view = 'filament.pages.reports.stock-summary';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Stock Summary';

    protected static ?string $title = 'Stock Summary';

    protected static ?int $navigationSort = 1;

    protected function getViewData(): array
    {
        return [
            'report' => app(ReportSummaryService::class)->stockSummary($this->from(), $this->to()),
        ];
    }
}
