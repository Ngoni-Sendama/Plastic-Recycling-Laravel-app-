<?php

namespace App\Filament\Resources\Buyers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BuyerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('buyer_name')
                    ->label('Buyer name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('contact_number')
                    ->label('Contact number')
                    ->tel()
                    ->maxLength(50),
            ]);
    }
}
