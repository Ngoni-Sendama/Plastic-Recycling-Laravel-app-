<?php

namespace App\Filament\Resources\PelletSales\Schemas;

use App\Models\PelletSale;
use App\Services\DocumentNumberGenerator;
use App\Services\PelletSaleCalculator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PelletSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale Details')
                    ->description('Record pellet sales to customers.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('receipt_number')
                            ->default(fn (): string => DocumentNumberGenerator::generate(new PelletSale(), 'receipt_number', 'SALE', today()))
                            ->placeholder('SALE-2026-0001')
                            ->helperText('Automatically generated with prefix SALE-YYYY-####.')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('customer_name')
                            ->placeholder('Metro Plastics')
                            ->required(),
                        Select::make('recorded_by_user_id')
                            ->relationship('recordedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(1),

                Section::make('Quantity And Amount')
                    ->description('Enter kg sold and unit price. Amount received is generated.')
                    ->schema([
                        TextInput::make('kg_sold')
                            ->placeholder('640')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateAmount($get, $set)),
                        TextInput::make('unit_price')
                            ->placeholder('0.95')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateAmount($get, $set)),
                        TextInput::make('amount_received')
                            ->placeholder('Generated from kg sold x unit price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(1),
            ]);
    }

    private static function updateAmount(Get $get, Set $set): null
    {
        $values = PelletSaleCalculator::calculate([
            'kg_sold' => $get('kg_sold'),
            'unit_price' => $get('unit_price'),
        ]);

        $set('amount_received', $values['amount_received']);

        return null;
    }
}
