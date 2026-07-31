<?php

namespace App\Filament\Resources\CrushingProductions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CrushingProductionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('batch_number'),
                TextEntry::make('materialIntake.id')
                    ->label('Material intake')
                    ->placeholder('-'),
                TextEntry::make('grn_reference')
                    ->placeholder('-'),
                TextEntry::make('material.name')
                    ->label('Material'),
                TextEntry::make('input_weight_kg')
                    ->numeric(),
                TextEntry::make('output_chips_kg')
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
