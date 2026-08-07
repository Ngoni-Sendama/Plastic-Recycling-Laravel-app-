<?php

namespace App\Filament\Resources\PrinterSettings\Pages;

use App\Filament\Resources\PrinterSettings\PrinterSettingResource;
use App\Models\PrinterSetting;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreatePrinterSetting extends CreateRecord
{
    protected static string $resource = PrinterSettingResource::class;

    protected string $view = 'filament.resources.printer-settings.pages.create-printer-setting';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        Log::info('Printer setting form submit started.', [
            'user_id' => $data['user_id'],
            'printer_name' => $data['printer_name'] ?? null,
        ]);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $setting = PrinterSetting::query()->updateOrCreate(
            ['user_id' => $data['user_id']],
            ['printer_name' => $data['printer_name']],
        );

        Log::info('Printer setting saved for user.', [
            'user_id' => $setting->user_id,
            'printer_name' => $setting->printer_name,
            'printer_setting_id' => $setting->id,
        ]);

        return $setting;
    }
}
