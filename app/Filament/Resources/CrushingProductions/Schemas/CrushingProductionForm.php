<?php

namespace App\Filament\Resources\CrushingProductions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CrushingProductionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('batch_number')
                    ->required(),
                Select::make('material_intake_id')
                    ->relationship('materialIntake', 'id'),
                TextInput::make('grn_reference'),
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->required(),
                TextInput::make('input_weight_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('output_chips_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('loss_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('loss_percentage')
                    ->required()
                    ->numeric(),
                Select::make('recorded_by_user_id')
                    ->relationship('recordedByUser', 'name'),
            ]);
    }
}
