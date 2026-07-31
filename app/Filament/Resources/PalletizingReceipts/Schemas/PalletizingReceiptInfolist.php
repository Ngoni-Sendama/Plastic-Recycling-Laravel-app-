<?php

namespace App\Filament\Resources\PalletizingReceipts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PalletizingReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('grn_number'),
                TextEntry::make('dispatch.id')
                    ->label('Dispatch')
                    ->placeholder('-'),
                TextEntry::make('dispatch_reference')
                    ->placeholder('-'),
                TextEntry::make('material.name')
                    ->label('Material'),
                TextEntry::make('weight_received_kg')
                    ->numeric(),
                TextEntry::make('rate_per_kg')
                    ->numeric(),
                TextEntry::make('amount_payable')
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
