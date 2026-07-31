<?php

namespace App\Filament\Resources\Materials\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaterialInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Material Details')
                    ->schema([
                        TextEntry::make('code'),
                        TextEntry::make('name'),
                    ])
                    ->columns(2),
                Section::make('Audit')
                    ->schema([
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
