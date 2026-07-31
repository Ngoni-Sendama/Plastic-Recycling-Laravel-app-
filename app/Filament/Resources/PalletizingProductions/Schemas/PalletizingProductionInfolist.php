<?php

namespace App\Filament\Resources\PalletizingProductions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PalletizingProductionInfolist
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
                        TextEntry::make('palletizingReceipt.grn_number')->label('Matched receipt')->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Production Output')
                    ->schema([
                        TextEntry::make('chips_input_kg')->numeric(),
                        TextEntry::make('pellets_output_kg')->numeric(),
                        TextEntry::make('loss_kg')->numeric(),
                        TextEntry::make('loss_percentage')->numeric(),
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
