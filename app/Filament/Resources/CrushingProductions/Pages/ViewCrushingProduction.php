<?php

namespace App\Filament\Resources\CrushingProductions\Pages;

use App\Filament\Resources\CrushingProductions\CrushingProductionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCrushingProduction extends ViewRecord
{
    protected static string $resource = CrushingProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
