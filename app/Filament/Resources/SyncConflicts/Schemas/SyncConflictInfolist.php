<?php

namespace App\Filament\Resources\SyncConflicts\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SyncConflictInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conflict Overview')
                    ->description('The server record was changed after the mobile device last synced. Review and resolve.')
                    ->schema([
                        TextEntry::make('table_name')
                            ->label('Table')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('record_id')
                            ->label('Server record ID'),
                        TextEntry::make('local_id')
                            ->label('Mobile local ID')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'resolved' => 'success',
                                'discarded' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('resolution')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'accept_submitted' => 'success',
                                'keep_server' => 'info',
                                'manual_merge' => 'warning',
                                'discard_submitted' => 'gray',
                                default => 'gray',
                            })
                            ->placeholder('-'),
                        TextEntry::make('submittedByUser.name')
                            ->label('Submitted by'),
                        TextEntry::make('resolvedByUser.name')
                            ->label('Resolved by')
                            ->placeholder('-'),
                        TextEntry::make('server_version')
                            ->label('Server lock version'),
                        TextEntry::make('submitted_version')
                            ->label('Submitted lock version'),
                        TextEntry::make('created_at')
                            ->label('Conflict created')
                            ->dateTime(),
                        TextEntry::make('resolved_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(4),

                Section::make('Changed Fields')
                    ->description('The fields edited on the mobile device that triggered this conflict.')
                    ->schema([
                        TextEntry::make('changed_fields')
                            ->badge()
                            ->listWithLineBreaks()
                            ->placeholder('No changed fields recorded'),
                    ]),

                Section::make('Server Record vs Submitted Change')
                    ->description('Compare the current server record with the conflicting mobile submission.')
                    ->columns(2)
                    ->schema([
                        KeyValueEntry::make('server_payload')
                            ->label('Current server record')
                            ->keyLabel('Field')
                            ->valueLabel('Server value'),
                        KeyValueEntry::make('submitted_payload')
                            ->label('Submitted mobile change')
                            ->keyLabel('Field')
                            ->valueLabel('Submitted value'),
                    ]),
            ]);
    }
}
