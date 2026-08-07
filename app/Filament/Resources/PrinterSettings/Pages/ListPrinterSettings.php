<?php

namespace App\Filament\Resources\PrinterSettings\Pages;

use App\Filament\Resources\PrinterSettings\PrinterSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrinterSettings extends ListRecords
{
    protected static string $resource = PrinterSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
