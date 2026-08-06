<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Expense;
use App\Services\DocumentNumberGenerator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Details')
                    ->description('Record a cash expense against the sales cash pool.')
                    ->schema([
                        DatePicker::make('date')
                            ->default(today())
                            ->required(),
                        TextInput::make('expense_number')
                            ->default(fn (): string => DocumentNumberGenerator::generate(new Expense, 'expense_number', 'EXP', today()))
                            ->placeholder('EXP-2026-0001')
                            ->helperText('Automatically generated with prefix EXP-YYYY-####.')
                            ->disabled()
                            ->dehydrated(),
                        Select::make('expense_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->activeRecord(),
                        Select::make('payment_method')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank Transfer' => 'Bank Transfer',
                                'EcoCash' => 'EcoCash',
                                'Card' => 'Card',
                            ])
                            ->placeholder('Select method'),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0.01),
                        Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Optional notes about this expense.'),
                        Select::make('recorded_by_user_id')
                            ->relationship('recordedByUser', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }
}
