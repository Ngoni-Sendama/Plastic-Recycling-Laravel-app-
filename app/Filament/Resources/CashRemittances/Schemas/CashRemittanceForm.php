<?php

namespace App\Filament\Resources\CashRemittances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CashRemittanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required(),
                TextInput::make('voucher_number')
                    ->required(),
                TextInput::make('period_covered'),
                TextInput::make('chips_delivered_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('recovery_price_per_kg')
                    ->required()
                    ->numeric(),
                TextInput::make('sales_revenue')
                    ->required()
                    ->numeric(),
                TextInput::make('cash_remitted')
                    ->required()
                    ->numeric(),
                TextInput::make('max_remittance_due')
                    ->required()
                    ->numeric(),
                TextInput::make('balance_retained')
                    ->required()
                    ->numeric(),
                Select::make('recorded_by_user_id')
                    ->relationship('recordedByUser', 'name'),
            ]);
    }
}
