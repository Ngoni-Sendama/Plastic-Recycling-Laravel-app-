<?php

namespace App\Filament\Resources\CrushingProductions\Schemas;

use App\Models\CrushingProduction;
use App\Services\CrushingProductionCalculator;
use App\Services\DocumentNumberGenerator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CrushingProductionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Details')
                    ->description('Record the crushing batch and source reference.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('batch_number')
                            ->default(fn (): string => DocumentNumberGenerator::generate(new CrushingProduction, 'batch_number', 'CR-BATCH', today()))
                            ->placeholder('CR-BATCH-2026-0001')
                            ->helperText('Automatically generated with prefix CR-BATCH-YYYY-####.')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('material_intake_id')
                            ->relationship('materialIntake', 'grn_number')
                            ->searchable()
                            ->preload(),
                        TextInput::make('grn_reference')
                            ->label('GRN Reference')
                            ->placeholder('GRN-2026-0001')
                            ->helperText('Optional reference to the source GRN.'),
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

                Section::make('Production Output')
                    ->description('Enter input and output weights. Loss figures are generated.')
                    ->schema([
                        TextInput::make('input_weight_kg')
                            ->placeholder('1170')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): null => self::updateLoss($get, $set)),
                        TextInput::make('output_chips_kg')
                            ->placeholder('1098.5')
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
                            ->placeholder('Generated as a percentage')
                            ->suffix('%')
                            ->formatStateUsing(fn ($state): ?string => $state === null || $state === '' ? null : number_format(((float) $state) * 100, 2))
                            ->dehydrateStateUsing(fn ($state): ?float => $state === null || $state === '' ? null : ((float) $state) / 100)
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),
            ]);
    }

    private static function updateLoss(Get $get, Set $set): null
    {
        $values = CrushingProductionCalculator::calculate([
            'input_weight_kg' => $get('input_weight_kg'),
            'output_chips_kg' => $get('output_chips_kg'),
        ]);

        $set('loss_kg', $values['loss_kg']);
        $set('loss_percentage', $values['loss_percentage']);

        return null;
    }
}
