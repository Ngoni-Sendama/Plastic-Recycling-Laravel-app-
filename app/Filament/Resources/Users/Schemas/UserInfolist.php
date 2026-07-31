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
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('username')->placeholder('-'),
                        TextEntry::make('role'),
                        TextEntry::make('email')->label('Email address'),
                    ])
                    ->columns(2),
                Section::make('Profile Settings')
                    ->schema([
                        TextEntry::make('avatar_url')->placeholder('-'),
                        TextEntry::make('locale')->placeholder('-'),
                        TextEntry::make('theme_color')->placeholder('-'),
                        TextEntry::make('custom_fields')->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Audit')
                    ->schema([
                        TextEntry::make('email_verified_at')->dateTime()->placeholder('-'),
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
