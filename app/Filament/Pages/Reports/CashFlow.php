<?php

namespace App\Filament\Pages\Reports;

use App\Services\CashFlowReportService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CashFlow extends BaseReportPage
{
    use HasPageShield;

    protected string $view = 'filament.pages.reports.cash-flow';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Cash Flow';

    protected static ?string $title = 'Cash Flow';

    protected static ?int $navigationSort = 5;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('exportCsv'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'report' => app(CashFlowReportService::class)->report($this->from(), $this->to()),
        ];
    }

    public function exportCsv(): mixed
    {
        $report = app(CashFlowReportService::class)->report($this->from(), $this->to());
        $filename = 'cash-flow-'.now()->timezone('Africa/Harare')->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Type', 'Reference', 'Description', 'Cash In', 'Cash Out', 'Running Balance']);

            foreach ($report['entries'] as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['type'],
                    $row['reference'],
                    Str::of($row['description'])->replace(["\r", "\n"], ' ')->trim(),
                    number_format((float) $row['cash_in'], 2, '.', ''),
                    number_format((float) $row['cash_out'], 2, '.', ''),
                    number_format((float) $row['balance'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
