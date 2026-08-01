<?php

namespace App\Filament\Resources\SyncConflicts\Tables;

use App\Services\SyncTableRegistry;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SyncConflictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50])
            ->columns([
                TextColumn::make('table_name')
                    ->label('Table')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('record_id')
                    ->label('Record'),
                TextColumn::make('local_id')
                    ->label('Local ID')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'resolved' => 'success',
                        'discarded' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('submittedByUser.name')
                    ->label('Submitted by')
                    ->searchable(),
                TextColumn::make('server_version')
                    ->label('Server v')
                    ->sortable(),
                TextColumn::make('submitted_version')
                    ->label('Submitted v')
                    ->sortable(),
                TextColumn::make('changed_fields')
                    ->label('Changed fields')
                    ->formatStateUsing(fn (?array $state): string => implode(', ', $state ?? []))
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'resolved' => 'Resolved',
                        'discarded' => 'Discarded',
                    ]),
                SelectFilter::make('table_name')
                    ->label('Table')
                    ->options(fn (): array => array_combine(array_keys(SyncTableRegistry::tables()), array_keys(SyncTableRegistry::tables()))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
