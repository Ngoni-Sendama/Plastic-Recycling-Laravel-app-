<?php

namespace App\Filament\Resources\PelletSales\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PelletSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('receipt_number')
                    ->required(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('kg_sold')
                    ->required()
                    ->numeric(),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('amount_received')
                    ->required()
                    ->numeric(),
                Select::make('recorded_by_user_id')
                    ->relationship('recordedByUser', 'name'),
            ]);
    }
}
