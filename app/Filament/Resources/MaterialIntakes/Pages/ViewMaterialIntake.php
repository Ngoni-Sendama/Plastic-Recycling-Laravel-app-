<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
                ->url(fn (): string => route('web.material-intakes.qz-print', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }

    protected function getViewData(): array
    {
        return [
        ];
    }
}
