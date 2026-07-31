<?php

namespace App\Filament\Resources\CrushingProductions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CrushingProductionsTable
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
                TextColumn::make('materialIntake.id')
                    ->searchable(),
                TextColumn::make('grn_reference')
                    ->searchable(),
                TextColumn::make('material.name')
                    ->searchable(),
                TextColumn::make('input_weight_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('output_chips_kg')
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
