<?php

namespace App\Filament\Resources\PelletSales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PelletSaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('receipt_number'),
                TextEntry::make('customer_name'),
                TextEntry::make('kg_sold')
                    ->numeric(),
                TextEntry::make('unit_price')
                    ->money(),
                TextEntry::make('amount_received')
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
