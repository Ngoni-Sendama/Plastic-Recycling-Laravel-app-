<?php

namespace App\Filament\Pages\Reports;

use App\Services\ReportSummaryService;
use Filament\Support\Icons\Heroicon;

class ProductionSummary extends BaseReportPage
{
    protected string $view = 'filament.pages.reports.production-summary';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static ?string $navigationLabel = 'Production Summary';

    protected static ?string $title = 'Production Summary';

    protected static ?int $navigationSort = 2;

    protected function getViewData(): array
    {
        return [
            'report' => app(ReportSummaryService::class)->productionSummary($this->from(), $this->to()),
        ];
    }
}
