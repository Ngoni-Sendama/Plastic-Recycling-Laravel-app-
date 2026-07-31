<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Textarea::make('custom_fields')
                    ->columnSpanFull(),
                TextInput::make('avatar_url')
                    ->url(),
                TextInput::make('locale'),
                TextInput::make('theme_color'),
                TextInput::make('username'),
                TextInput::make('role')
                    ->required()
                    ->default('Admin'),
            ]);
    }
}
