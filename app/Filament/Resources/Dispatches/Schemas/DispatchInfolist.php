<?php

namespace App\Filament\Resources\Dispatches\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DispatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('dispatch_note_number'),
                TextEntry::make('crushingProduction.id')
                    ->label('Crushing production')
                    ->placeholder('-'),
                TextEntry::make('batch_reference')
                    ->placeholder('-'),
                TextEntry::make('material.name')
                    ->label('Material'),
                TextEntry::make('weight_dispatched_kg')
                    ->numeric(),
                TextEntry::make('transported_by')
                    ->placeholder('-'),
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
