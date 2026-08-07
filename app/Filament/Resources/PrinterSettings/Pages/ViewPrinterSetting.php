<?php

declare(strict_types=1);

namespace App\Filament\Resources\PrinterSettings\Pages;

use App\Filament\Resources\PrinterSettings\PrinterSettingResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPrinterSetting extends ViewRecord
{
    protected static string $resource = PrinterSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('thermalPrint')
                ->label('Test Thermal Printer')
                ->icon('heroicon-m-printer')
                ->color('info')
                ->url(fn (): string => route('web.thermal-test-print'))
                ->openUrlInNewTab(),
        ];
    }
}
