<?php

namespace App\Filament\Resources\PalletizingReceipts\Pages;

use App\Filament\Resources\PalletizingReceipts\PalletizingReceiptResource;
use App\Services\RecordThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPalletizingReceipt extends ViewRecord
{
    protected static string $resource = PalletizingReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(RecordThermalPrinter::class)->print('PALLETIZING RECEIPT', [
                            'Date' => (string) ($this->record->date?->toDateString() ?? '-'),
                            'Receipt No' => (string) ($this->record->grn_number ?? $this->record->receipt_number ?? '-'),
                            'Dispatch No' => (string) ($this->record->dispatch?->dispatch_note_number ?? '-'),
                            'Weight Received' => number_format((float) ($this->record->weight_received_kg ?? 0), 2).' kg',
                            'Amount Payable' => '$'.number_format((float) ($this->record->amount_payable ?? 0), 2),
                        ]);

                        Notification::make()->title('Thermal receipt sent to printer.')->success()->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()->title('Unable to print palletizing receipt.')->body($throwable->getMessage())->danger()->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
