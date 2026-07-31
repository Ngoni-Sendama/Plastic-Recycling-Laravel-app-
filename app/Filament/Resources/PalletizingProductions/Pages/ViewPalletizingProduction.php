<?php

namespace App\Filament\Resources\PalletizingProductions\Pages;

use App\Filament\Resources\PalletizingProductions\PalletizingProductionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPalletizingProduction extends ViewRecord
{
    protected static string $resource = PalletizingProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
