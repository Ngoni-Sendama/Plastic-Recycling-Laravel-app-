<?php

namespace App\Filament\Resources\PalletizingProductions\Pages;

use App\Filament\Resources\PalletizingProductions\PalletizingProductionResource;
use App\Services\RecordThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPalletizingProduction extends ViewRecord
{
    protected static string $resource = PalletizingProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn () => route('web.palletizing-productions.pdf', $this->record))
                ->openUrlInNewTab(),
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(RecordThermalPrinter::class)->print('PALLETIZING PRODUCTION', [
                            'Date' => (string) ($this->record->date?->toDateString() ?? '-'),
                            'Batch No' => (string) ($this->record->batch_number ?? '-'),
                            'GRN Reference' => (string) ($this->record->grn_reference ?? '-'),
                            'Chips Input' => number_format((float) ($this->record->chips_input_kg ?? 0), 2).' kg',
                            'Pellets Output' => number_format((float) ($this->record->pellets_output_kg ?? 0), 2).' kg',
                            'Loss %' => number_format((float) (($this->record->loss_percentage ?? 0) * 100), 2).'%',
                        ]);

                        Notification::make()->title('Thermal receipt sent to printer.')->success()->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()->title('Unable to print palletizing production.')->body($throwable->getMessage())->danger()->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
