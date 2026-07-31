<?php

namespace App\Filament\Resources\CashRemittances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashRemittancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('voucher_number')
                    ->searchable(),
                TextColumn::make('period_covered')
                    ->searchable(),
                TextColumn::make('chips_delivered_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('recovery_price_per_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sales_revenue')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cash_remitted')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_remittance_due')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_retained')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('recordedByUser.name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
