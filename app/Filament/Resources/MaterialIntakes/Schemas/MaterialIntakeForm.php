<?php

namespace App\Filament\Resources\MaterialIntakes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MaterialIntakeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('grn_number')
                    ->required(),
                TextInput::make('buyer_name')
                    ->required(),
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->required(),
                TextInput::make('gross_weight_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('tare_weight_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('net_weight_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('total_value')
                    ->required()
                    ->numeric(),
                Select::make('recorded_by_user_id')
                    ->relationship('recordedByUser', 'name'),
            ]);
    }
}
