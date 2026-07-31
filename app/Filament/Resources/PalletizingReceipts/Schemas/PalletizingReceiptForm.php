<?php

namespace App\Filament\Resources\PalletizingReceipts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PalletizingReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('grn_number')
                    ->required(),
                Select::make('dispatch_id')
                    ->relationship('dispatch', 'id'),
                TextInput::make('dispatch_reference'),
                Select::make('material_id')
                    ->relationship('material', 'name')
                    ->required(),
                TextInput::make('weight_received_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('rate_per_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('amount_payable')
                    ->required()
                    ->numeric(),
                Select::make('recorded_by_user_id')
                    ->relationship('recordedByUser', 'name'),
            ]);
    }
}
