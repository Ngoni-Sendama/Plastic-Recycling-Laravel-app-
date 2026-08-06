<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('expense_number')->label('Expense Number'),
                        TextEntry::make('category.name')->label('Category'),
                        TextEntry::make('payment_method')->label('Payment Method')->placeholder('-'),
                        TextEntry::make('amount')->numeric()->prefix('$'),
                        TextEntry::make('description')->placeholder('-'),
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
