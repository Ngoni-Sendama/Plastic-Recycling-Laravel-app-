<?php

namespace App\Filament\Resources\Dispatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DispatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('dispatch_note_number')
                    ->searchable(),
                TextColumn::make('crushingProduction.id')
                    ->searchable(),
                TextColumn::make('batch_reference')
                    ->searchable(),
                TextColumn::make('material.name')
                    ->searchable(),
                TextColumn::make('weight_dispatched_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('transported_by')
                    ->searchable(),
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
