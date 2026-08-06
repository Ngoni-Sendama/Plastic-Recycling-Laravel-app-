<?php

namespace App\Filament\Resources\CrushingProductions\Pages;

use App\Filament\Resources\CrushingProductions\CrushingProductionResource;
use App\Services\RecordThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCrushingProduction extends ViewRecord
{
    protected static string $resource = CrushingProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn () => route('exports.crushing-production.pdf', $this->record))
                ->openUrlInNewTab(),
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(RecordThermalPrinter::class)->print('CRUSHING PRODUCTION', [
                            'Date' => (string) ($this->record->date?->toDateString() ?? '-'),
                            'Batch No' => (string) ($this->record->batch_number ?? '-'),
                            'GRN Reference' => (string) ($this->record->grn_reference ?? '-'),
                            'Material' => (string) ($this->record->material?->name ?? '-'),
                            'Input Weight' => number_format((float) ($this->record->input_weight_kg ?? 0), 2).' kg',
                            'Output Chips' => number_format((float) ($this->record->output_chips_kg ?? 0), 2).' kg',
                            'Loss %' => number_format((float) (($this->record->loss_percentage ?? 0) * 100), 2).'%',
                        ]);

                        Notification::make()->title('Thermal receipt sent to printer.')->success()->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()->title('Unable to print crushing production.')->body($throwable->getMessage())->danger()->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
