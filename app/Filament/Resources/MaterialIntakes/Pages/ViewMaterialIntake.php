<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialIntake extends ViewRecord
{
    protected static string $resource = MaterialIntakeResource::class;

    protected string $view = 'filament.resources.material-intakes.pages.view-material-intake';

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
                    $this->dispatch('material-intake-qz-print');

                    Notification::make()
                        ->title('Print request sent to the browser.')
                        ->body('QZ Tray on this computer will handle the receipt if it is installed and connected to the printer.')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'printPayload' => [
                'title' => 'Material Intake / Goods Received Note',
                'form' => 'Form CR-01 - Crushing Office',
                'company' => 'HIGHGLEN PLASTIC INDUSTRIES',
                'date' => (string) ($this->record->date?->toDateString() ?? '-'),
                'grnNumber' => (string) ($this->record->grn_number ?? '-'),
                'buyerName' => (string) ($this->record->buyer?->buyer_name ?? $this->record->buyer_name ?? '-'),
                'buyerContact' => (string) ($this->record->buyer?->contact_number ?? '-'),
                'material' => (string) ($this->record->material?->code ? $this->record->material->code.' - '.$this->record->material->name : ($this->record->material?->name ?? '-')),
                'grossWeight' => number_format((float) ($this->record->gross_weight_kg ?? 0), 3),
                'tareWeight' => number_format((float) ($this->record->tare_weight_kg ?? 0), 3),
                'netWeight' => number_format((float) ($this->record->net_weight_kg ?? 0), 3),
                'unitPrice' => number_format((float) ($this->record->unit_price ?? 0), 2),
                'totalValue' => number_format((float) ($this->record->total_value ?? 0), 2),
                'remarks' => (string) ($this->record->remarks ?? $this->record->note ?? '-'),
                'recordedBy' => (string) ($this->record->recordedByUser?->name ?? '-'),
            ],
        ];
    }
}
