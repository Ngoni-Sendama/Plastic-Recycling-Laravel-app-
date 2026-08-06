<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use App\Services\RecordThermalPrinter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialIntake extends ViewRecord
{
    protected static string $resource = MaterialIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->url(fn () => route('web.material-intakes.pdf', $this->record))
                ->openUrlInNewTab(),
            Action::make('thermalPrint')
                ->label('Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->action(function (): void {
                    try {
                        app(RecordThermalPrinter::class)->print('MATERIAL INTAKE', [
                            'Date' => (string) ($this->record->date?->toDateString() ?? '-'),
                            'GRN No' => (string) ($this->record->grn_number ?? '-'),
                            'Buyer' => (string) ($this->record->buyer_name ?? $this->record->buyer?->name ?? '-'),
                            'Material' => (string) ($this->record->material?->name ?? '-'),
                            'Net Weight' => number_format((float) ($this->record->net_weight_kg ?? 0), 2).' kg',
                            'Total Value' => '$'.number_format((float) ($this->record->total_value ?? 0), 2),
                        ]);

                        Notification::make()->title('Thermal receipt sent to printer.')->success()->send();
                    } catch (\Throwable $throwable) {
                        Notification::make()->title('Unable to print material intake.')->body($throwable->getMessage())->danger()->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
