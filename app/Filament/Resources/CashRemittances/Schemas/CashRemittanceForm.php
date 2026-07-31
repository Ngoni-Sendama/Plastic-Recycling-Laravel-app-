<?php

namespace App\Filament\Resources\CashRemittances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CashRemittanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voucher Details')
                    ->description('Record cash remitted for a production and sales period.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('voucher_number')
                            ->placeholder('REM-2026-0001')
                            ->required(),
                        TextInput::make('period_covered')
                            ->placeholder('2026-07-31 to 2026-08-02'),
                        Select::make('recorded_by_user_id')
                            ->relationship('recordedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(2),

                Section::make('Cash Reconciliation')
                    ->description('Enter delivered chips, recovery price, sales revenue, and remitted cash. Due and retained values are generated.')
                    ->schema([
                        TextInput::make('chips_delivered_kg')
                            ->placeholder('1087.5')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('recovery_price_per_kg')
                            ->placeholder('0.18')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('sales_revenue')
                            ->placeholder('608')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('cash_remitted')
                            ->placeholder('500')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateTotals($get, $set)),
                        TextInput::make('max_remittance_due')
                            ->placeholder('Generated from chips x recovery price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('balance_retained')
                            ->placeholder('Generated from sales revenue - cash remitted')
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
        $chipsDelivered = (float) ($get('chips_delivered_kg') ?? 0);
        $recoveryPrice = (float) ($get('recovery_price_per_kg') ?? 0);
        $salesRevenue = (float) ($get('sales_revenue') ?? 0);
        $cashRemitted = (float) ($get('cash_remitted') ?? 0);

        $set('max_remittance_due', round($chipsDelivered * $recoveryPrice, 2));
        $set('balance_retained', round($salesRevenue - $cashRemitted, 2));

        return null;
    }
}
