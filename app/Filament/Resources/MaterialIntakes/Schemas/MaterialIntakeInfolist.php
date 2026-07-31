<?php

namespace App\Filament\Resources\MaterialIntakes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MaterialIntakeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('grn_number'),
                TextEntry::make('buyer_name'),
                TextEntry::make('material.name')
                    ->label('Material'),
                TextEntry::make('gross_weight_kg')
                    ->numeric(),
                TextEntry::make('tare_weight_kg')
                    ->numeric(),
                TextEntry::make('net_weight_kg')
                    ->numeric(),
                TextEntry::make('unit_price')
                    ->money(),
                TextEntry::make('total_value')
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
