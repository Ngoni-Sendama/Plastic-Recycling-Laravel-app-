<?php

namespace App\Filament\Resources\PalletizingProductions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PalletizingProductionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('batch_number'),
                TextEntry::make('palletizingReceipt.id')
                    ->label('Palletizing receipt')
                    ->placeholder('-'),
                TextEntry::make('grn_reference')
                    ->placeholder('-'),
                TextEntry::make('chips_input_kg')
                    ->numeric(),
                TextEntry::make('pellets_output_kg')
                    ->numeric(),
                TextEntry::make('loss_kg')
                    ->numeric(),
                TextEntry::make('loss_percentage')
                    ->numeric(),
                TextEntry::make('recordedByUser.name')
                    ->label('Recorded by user')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
