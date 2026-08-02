<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action')
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('source')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->headerActions([])
            ->toolbarActions([]);
    }
}
