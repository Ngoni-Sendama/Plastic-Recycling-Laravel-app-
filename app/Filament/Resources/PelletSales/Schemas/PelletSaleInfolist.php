<?php

namespace App\Filament\Resources\PelletSales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PelletSaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('receipt_number')->label('Receipt Number'),
                        TextEntry::make('customer_name'),
                    ])
                    ->columns(3),
                Section::make('Quantity And Amount')
                    ->schema([
                        TextEntry::make('kg_sold')->numeric(),
                        TextEntry::make('unit_price')->numeric(),
                        TextEntry::make('amount_received')->numeric(),
                    ])
                    ->columns(3),
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
