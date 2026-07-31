<?php

namespace App\Filament\Resources\PalletizingProductions\Pages;

use App\Filament\Resources\PalletizingProductions\PalletizingProductionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPalletizingProductions extends ListRecords
{
    protected static string $resource = PalletizingProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
