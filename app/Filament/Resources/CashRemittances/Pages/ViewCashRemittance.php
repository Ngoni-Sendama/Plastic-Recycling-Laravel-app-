<?php

namespace App\Filament\Resources\CashRemittances\Pages;

use App\Filament\Resources\CashRemittances\CashRemittanceResource;
use App\Services\RecordThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRemittance extends ViewRecord
{
    protected static string $resource = CashRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn () => route('web.cash-remittances.pdf', $this->record))
                ->openUrlInNewTab(),
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(RecordThermalPrinter::class)->print('CASH REMITTANCE', [
                            'Date' => (string) ($this->record->date?->toDateString() ?? '-'),
                            'Voucher No' => (string) ($this->record->voucher_number ?? '-'),
                            'Sales Revenue' => '$'.number_format((float) ($this->record->sales_revenue ?? 0), 2),
                            'Cash Remitted' => '$'.number_format((float) ($this->record->cash_remitted ?? 0), 2),
                            'Balance Retained' => '$'.number_format((float) ($this->record->balance_retained ?? 0), 2),
                        ]);

                        Notification::make()->title('Thermal receipt sent to printer.')->success()->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()->title('Unable to print cash remittance.')->body($throwable->getMessage())->danger()->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
