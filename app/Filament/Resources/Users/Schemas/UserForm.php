<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Details')
                    ->description('Basic identity and login information for the staff member.')
                    ->schema([
                        TextInput::make('name')
                            ->placeholder('Tawanda Moyo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('username')
                            ->placeholder('crusher01')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('Email address')
                            ->placeholder('crusher01@example.com')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        // New Code fetching dynamically from the roles table using relationship
                        CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->columnSpanFull()
                            ->columns(3)
                            ->searchable(),
                        // Old code
                        // Select::make('role')
                        //     ->options([
                        //         'Admin' => 'Admin',
                        //         'Stock controller' => 'Stock controller',
                        //         'Crusher operator' => 'Crusher operator',
                        //         'Stock receiver' => 'Stock receiver',
                        //         'Palletizing operator' => 'Palletizing operator',
                        //         'Supervisor' => 'Supervisor',
                        //     ])
                        //     ->default('Stock controller')
                        //     ->required(),
                    ])
                    ->columns(2),

                Section::make('Security')
                    ->description('Set the login password. Leave blank while editing to keep the current password.')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->placeholder('Minimum 8 characters')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->maxLength(255),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email verified at')
                            ->placeholder('Leave blank if not verified'),
                    ])
                    ->columns(2),

                Section::make('Preferences')
                    ->description('Optional display preferences used by the admin profile plugin.')
                    ->schema([
                        TextInput::make('locale')
                            ->placeholder('en'),
                        TextInput::make('theme_color')
                            ->placeholder('amber'),
                    ])
                    ->columns(2),
            ]);
    }
}
