<?php

namespace App\Filament\Resources\PalletizingReceipts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PalletizingReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('grn_number')
                    ->searchable(),
                TextColumn::make('dispatch.id')
                    ->searchable(),
                TextColumn::make('dispatch_reference')
                    ->searchable(),
                TextColumn::make('material.name')
                    ->searchable(),
                TextColumn::make('weight_received_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rate_per_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount_payable')
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
