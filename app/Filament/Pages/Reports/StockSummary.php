<?php

namespace App\Filament\Pages\Reports;

use App\Services\ReportSummaryService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class StockSummary extends BaseReportPage
{
    use HasPageShield;

    protected string $view = 'filament.pages.reports.stock-summary';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Stock Summary';

    protected static ?string $title = 'Stock Summary';

    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportWorkbook')
                ->label('Export Workbook')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('exports.stock-cash-control')),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'report' => app(ReportSummaryService::class)->stockSummary($this->from(), $this->to()),
        ];
    }
}
