<?php

namespace App\Filament\Resources\MaterialIntakes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MaterialIntakeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt Details')
                    ->description('Record the incoming raw material and supplier details.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('grn_number')
                            ->label('GRN Number')
                            ->placeholder('GRN-2026-0001')
                            ->required(),
                        TextInput::make('buyer_name')
                            ->placeholder('GreenCycle Suppliers')
                            ->required(),
                        Select::make('material_id')
                            ->relationship('material', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('recorded_by_user_id')
                            ->relationship('recordedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(2),

                Section::make('Weights And Value')
                    ->description('Enter gross, tare, and unit price. Net weight and total value are generated.')
                    ->schema([
                        TextInput::make('gross_weight_kg')
                            ->placeholder('1250')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('tare_weight_kg')
                            ->placeholder('80')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('unit_price')
                            ->placeholder('0.42')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('net_weight_kg')
                            ->placeholder('Generated from gross - tare')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('total_value')
                            ->placeholder('Generated from net weight x unit price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(3),
            ]);
    }

    private static function updateTotals(Get $get, Set $set): null
    {
        $grossWeight = (float) ($get('gross_weight_kg') ?? 0);
        $tareWeight = (float) ($get('tare_weight_kg') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $netWeight = max($grossWeight - $tareWeight, 0);

        $set('net_weight_kg', round($netWeight, 3));
        $set('total_value', round($netWeight * $unitPrice, 2));

        return null;
    }
}
