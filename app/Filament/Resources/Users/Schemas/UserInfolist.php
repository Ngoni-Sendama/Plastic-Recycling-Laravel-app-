<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')
                    ->description('Staff identity and access level.')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('username')->placeholder('-'),
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Admin' => 'danger',
                                'Supervisor' => 'warning',
                                'Stock controller' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('email')->label('Email address'),
                    ])
                    ->columns(2),
                Section::make('Preferences')
                    ->description('Optional profile and display settings.')
                    ->schema([
                        TextEntry::make('avatar_url')->placeholder('-'),
                        TextEntry::make('locale')->placeholder('-'),
                        TextEntry::make('theme_color')->placeholder('-'),
                        TextEntry::make('custom_fields')->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Audit')
                    ->description('System timestamps for this account.')
                    ->schema([
                        TextEntry::make('email_verified_at')->dateTime()->placeholder('-'),
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
