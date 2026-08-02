<?php

namespace App\Filament\Resources\MaterialIntakes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaterialIntakeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('grn_number')->label('GRN Number'),
                        TextEntry::make('buyer.buyer_name')->label('Buyer'),
                        TextEntry::make('buyer.contact_number')->label('Buyer contact')->placeholder('-'),
                        TextEntry::make('material.name')->label('Material'),
                    ])
                    ->columns(2),
                Section::make('Weights And Value')
                    ->schema([
                        TextEntry::make('gross_weight_kg')->numeric(),
                        TextEntry::make('tare_weight_kg')->numeric(),
                        TextEntry::make('net_weight_kg')->numeric(),
                        TextEntry::make('unit_price')->numeric(),
                        TextEntry::make('total_value')->numeric(),
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
