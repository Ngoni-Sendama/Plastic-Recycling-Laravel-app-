<?php

namespace App\Filament\Resources\Dispatches\Schemas;

use App\Models\Dispatch;
use App\Services\DocumentNumberGenerator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DispatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dispatch Details')
                    ->description('Record chips dispatched from crushing to palletizing.')
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('dispatch_note_number')
                            ->default(fn (): string => DocumentNumberGenerator::generate(new Dispatch(), 'dispatch_note_number', 'DN', today()))
                            ->placeholder('DN-2026-0001')
                            ->helperText('Automatically generated with prefix DN-YYYY-####.')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('crushing_production_id')
                            ->relationship('crushingProduction', 'batch_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('batch_reference')
                            ->placeholder('CR-BATCH-2026-0001')
                            ->helperText('Optional reference to the source batch.'),
                        Select::make('material_id')
                            ->relationship('material', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('weight_dispatched_kg')
                            ->placeholder('1090')
                            ->required()
                            ->numeric(),
                        TextInput::make('transported_by')
                            ->placeholder('Highglen Truck 1'),
                        Select::make('recorded_by_user_id')
                            ->relationship('recordedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }
}
