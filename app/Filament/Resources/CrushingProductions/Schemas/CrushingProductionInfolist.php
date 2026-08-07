<?php

namespace App\Filament\Resources\CrushingProductions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CrushingProductionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('batch_number'),
                        TextEntry::make('grn_reference')->label('GRN Reference')->placeholder('-'),
                        TextEntry::make('material.name')->label('Material'),
                        TextEntry::make('materialIntake.grn_number')->label('Matched intake')->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Production Output')
                    ->schema([
                        TextEntry::make('input_weight_kg')->numeric(),
                        TextEntry::make('output_chips_kg')->numeric(),
                        TextEntry::make('loss_kg')->numeric(),
                        TextEntry::make('loss_percentage')
                            ->label('Loss percentage')
                            ->formatStateUsing(fn ($state): string => number_format(((float) $state) * 100, 2).'%'),
                    ])
                    ->columns(4),
                Section::make('Audit')
                    ->schema([
                        TextEntry::make('recordedByUser.name')->label('Recorded by')->placeholder('-'),
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
