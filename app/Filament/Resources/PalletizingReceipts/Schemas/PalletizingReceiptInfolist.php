<?php

namespace App\Filament\Resources\PalletizingReceipts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PalletizingReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('grn_number')->label('GRN Number'),
                        TextEntry::make('dispatch_reference')->placeholder('-'),
                        TextEntry::make('dispatch.dispatch_note_number')->label('Matched dispatch')->placeholder('-'),
                        TextEntry::make('material.name')->label('Material'),
                    ])
                    ->columns(2),
                Section::make('Quantity And Payable')
                    ->schema([
                        TextEntry::make('weight_received_kg')->numeric(),
                        TextEntry::make('rate_per_kg')->numeric(),
                        TextEntry::make('amount_payable')->numeric(),
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
