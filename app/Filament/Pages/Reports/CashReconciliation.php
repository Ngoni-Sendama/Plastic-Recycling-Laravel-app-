<?php

namespace App\Filament\Pages\Reports;

use App\Services\ReportSummaryService;
use Filament\Support\Icons\Heroicon;

class CashReconciliation extends BaseReportPage
{
    protected string $view = 'filament.pages.reports.cash-reconciliation';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Cash Reconciliation';

    protected static ?string $title = 'Cash Reconciliation';

    protected static ?int $navigationSort = 4;

    protected function getViewData(): array
    {
        return [
            'report' => app(ReportSummaryService::class)->cashReconciliation($this->from(), $this->to()),
        ];
    }
}
