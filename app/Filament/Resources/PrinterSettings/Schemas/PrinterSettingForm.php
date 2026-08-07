<?php

namespace App\Filament\Resources\PrinterSettings\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrinterSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Printer Assignment')
                    ->description('Save the QZ Tray printer for the authenticated user.')
                    ->schema([
                        Hidden::make('user_id'),
                        TextInput::make('printer_name')
                            ->label('Printer name')
                            ->helperText('Auto-filled from QZ Tray.')
                            ->required(),
                    ]),
            ]);
    }
}
