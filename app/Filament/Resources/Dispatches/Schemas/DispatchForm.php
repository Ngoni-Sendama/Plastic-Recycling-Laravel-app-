<?php

namespace App\Filament\Resources\Dispatches\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DispatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('dispatch_note_number')
                    ->required(),
                Select::make('crushing_production_id')
                    ->relationship('crushingProduction', 'id'),
                TextInput::make('batch_reference'),
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->required(),
                TextInput::make('weight_dispatched_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('transported_by'),
                Select::make('recorded_by_user_id')
                    ->relationship('recordedByUser', 'name'),
            ]);
    }
}
