<?php

namespace App\Filament\Resources\MaterialIntakes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialIntakesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50])
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('grn_number')
                    ->label('GRN')
                    ->searchable(),
                TextColumn::make('material.name')
                    ->label('Material')
                    ->searchable(),
                TextColumn::make('buyer.buyer_name')
                    ->label('Buyer')
                    ->searchable(),
                TextColumn::make('net_weight_kg')
                    ->label('Net kg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_value')
                    ->label('Value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gross_weight_kg')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tare_weight_kg')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit_price')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recordedByUser.name')
                    ->label('Recorded by')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('buyer')
                    ->relationship('buyer', 'buyer_name'),
                SelectFilter::make('material')
                    ->relationship('material', 'name'),
                SelectFilter::make('recordedByUser')
                    ->label('Recorded by')
                    ->relationship('recordedByUser', 'name'),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('date', '<=', $date))),
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
