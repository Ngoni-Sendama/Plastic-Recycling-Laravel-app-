<?php

namespace App\Filament\Resources\Dispatches\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DispatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dispatch Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('dispatch_note_number')->label('Dispatch Note'),
                        TextEntry::make('batch_reference')->placeholder('-'),
                        TextEntry::make('crushingProduction.batch_number')->label('Matched batch')->placeholder('-'),
                        TextEntry::make('material.name')->label('Material'),
                        TextEntry::make('transported_by')->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Quantity')
                    ->schema([
                        TextEntry::make('weight_dispatched_kg')->numeric(),
                    ]),
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
