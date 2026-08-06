<?php

namespace App\Filament\Resources\ExpenseCategories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Details')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('description')->placeholder('-'),
                        TextEntry::make('is_active')
                            ->label('Active')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                    ])
                    ->columns(3),
            ]);
    }
}
