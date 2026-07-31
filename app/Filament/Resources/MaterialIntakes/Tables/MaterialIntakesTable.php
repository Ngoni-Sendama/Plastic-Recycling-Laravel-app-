<?php

namespace App\Filament\Resources\MaterialIntakes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaterialIntakesTable
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
                TextColumn::make('buyer_name')
                    ->searchable(),
                TextColumn::make('material.name')
                    ->searchable(),
                TextColumn::make('gross_weight_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tare_weight_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_weight_kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('total_value')
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
