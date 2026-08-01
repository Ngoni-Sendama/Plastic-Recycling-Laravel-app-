<?php

namespace App\Filament\Resources\PalletizingProductions\Schemas;

use App\Services\PalletizingProductionCalculator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PalletizingProductionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Details')
                    ->description('Record pelletizing production batch details.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('batch_number')
                            ->placeholder('PL-BATCH-0001')
                            ->required(),
                        Select::make('palletizing_receipt_id')
                            ->relationship('palletizingReceipt', 'grn_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('grn_reference')
                            ->label('GRN Reference')
                            ->placeholder('PGRN-2026-0001'),
                        Select::make('recorded_by_user_id')
                            ->relationship('recordedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(2),

                Section::make('Production Output')
                    ->description('Enter input and output weights. Loss figures are generated.')
                    ->schema([
                        TextInput::make('chips_input_kg')
                            ->placeholder('1087.5')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateLoss($get, $set)),
                        TextInput::make('pellets_output_kg')
                            ->placeholder('1018.2')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateLoss($get, $set)),
                        TextInput::make('loss_kg')
                            ->placeholder('Generated from input - output')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('loss_percentage')
                            ->placeholder('Generated as a ratio')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(4),
            ]);
    }

    private static function updateLoss(Get $get, Set $set): null
    {
        $values = PalletizingProductionCalculator::calculate([
            'chips_input_kg' => $get('chips_input_kg'),
            'pellets_output_kg' => $get('pellets_output_kg'),
        ]);

        $set('loss_kg', $values['loss_kg']);
        $set('loss_percentage', $values['loss_percentage']);

        return null;
    }
}
