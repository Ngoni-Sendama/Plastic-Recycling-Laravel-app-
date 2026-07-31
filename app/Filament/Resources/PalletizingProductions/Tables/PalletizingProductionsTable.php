<?php

namespace App\Filament\Resources\PalletizingProductions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PalletizingProductionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->searchable(),
                TextColumn::make('palletizingReceipt.id')
                    ->searchable(),
                TextColumn::make('grn_reference')
                    ->searchable(),
                TextColumn::make('chips_input_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pellets_output_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('loss_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('loss_percentage')
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
