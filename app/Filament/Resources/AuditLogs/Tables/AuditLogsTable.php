<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginated([25, 50, 100])
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('action')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('source')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('auditable_type')
                    ->label('Model')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('auditable_id')
                    ->label('Model ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ]),
                SelectFilter::make('source')
                    ->options([
                        'web' => 'Web',
                        'mobile_api' => 'Mobile API',
                    ]),
                Filter::make('only_my_records')
                    ->label('Only my records')
                    ->query(fn (Builder $query): Builder => $query->where('user_id', auth()->id())),
            ])
            ->recordActions([]);
    }
}
