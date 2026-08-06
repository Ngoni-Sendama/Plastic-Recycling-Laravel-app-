<?php

namespace App\Filament\Resources\Dispatches\Pages;

use App\Filament\Resources\Dispatches\DispatchResource;
use App\Services\RecordThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewDispatch extends ViewRecord
{
    protected static string $resource = DispatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn () => route('web.dispatches.pdf', $this->record))
                ->openUrlInNewTab(),
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(RecordThermalPrinter::class)->print('DISPATCH', [
                            'Date' => (string) ($this->record->date?->toDateString() ?? '-'),
                            'Dispatch No' => (string) ($this->record->dispatch_note_number ?? '-'),
                            'Material' => (string) ($this->record->material?->name ?? '-'),
                            'Weight Dispatched' => number_format((float) ($this->record->weight_dispatched_kg ?? 0), 2).' kg',
                        ]);

                        Notification::make()->title('Thermal receipt sent to printer.')->success()->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()->title('Unable to print dispatch.')->body($throwable->getMessage())->danger()->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
