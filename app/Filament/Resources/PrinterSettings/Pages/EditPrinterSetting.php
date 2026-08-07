<?php

namespace App\Filament\Resources\PrinterSettings\Pages;

use App\Filament\Resources\PrinterSettings\PrinterSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrinterSetting extends EditRecord
{
    protected static string $resource = PrinterSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
