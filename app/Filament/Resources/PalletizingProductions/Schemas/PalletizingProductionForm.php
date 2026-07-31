<?php

namespace App\Filament\Resources\PalletizingProductions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PalletizingProductionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('batch_number')
                    ->required(),
                Select::make('palletizing_receipt_id')
                    ->relationship('palletizingReceipt', 'id'),
                TextInput::make('grn_reference'),
                TextInput::make('chips_input_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('pellets_output_kg')
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
