<?php

namespace App\Filament\Resources\PalletizingReceipts\Schemas;

use App\Models\PalletizingReceipt;
use App\Services\DocumentNumberGenerator;
use App\Services\PalletizingReceiptCalculator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PalletizingReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt Details')
                    ->description('Record chips received at palletizing.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('grn_number')
                            ->label('GRN Number')
                            ->default(fn (): string => DocumentNumberGenerator::generate(new PalletizingReceipt(), 'grn_number', 'PGRN', today()))
                            ->placeholder('PGRN-2026-0001')
                            ->helperText('Automatically generated with prefix PGRN-YYYY-####.')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('dispatch_id')
                            ->relationship('dispatch', 'dispatch_note_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('dispatch_reference')
                            ->placeholder('DN-2026-0001')
                            ->helperText('Optional reference to the source dispatch.'),
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

                Section::make('Quantity And Payable')
                    ->description('Enter received weight and rate. Amount payable is generated.')
                    ->schema([
                        TextInput::make('weight_received_kg')
                            ->placeholder('1087.5')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateAmount($get, $set)),
                        TextInput::make('rate_per_kg')
                            ->placeholder('0.18')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateAmount($get, $set)),
                        TextInput::make('amount_payable')
                            ->placeholder('Generated from weight x rate')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(1),
            ]);
    }

    private static function updateAmount(Get $get, Set $set): null
    {
        $values = PalletizingReceiptCalculator::calculate([
            'weight_received_kg' => $get('weight_received_kg'),
            'rate_per_kg' => $get('rate_per_kg'),
        ]);

        $set('amount_payable', $values['amount_payable']);

        return null;
    }
}
