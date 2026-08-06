<?php

namespace App\Filament\Resources\PelletSales\Pages;

use App\Filament\Resources\PelletSales\PelletSaleResource;
use App\Services\PelletSaleThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPelletSale extends ViewRecord
{
    protected static string $resource = PelletSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn () => route('web.pellet-sales.pdf', $this->record))
                ->openUrlInNewTab(),
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(PelletSaleThermalPrinter::class)->print($this->record);

                        Notification::make()
                            ->title('Thermal receipt sent to printer.')
                            ->success()
                            ->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()
                            ->title('Unable to print pellet sale receipt.')
                            ->body($throwable->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
