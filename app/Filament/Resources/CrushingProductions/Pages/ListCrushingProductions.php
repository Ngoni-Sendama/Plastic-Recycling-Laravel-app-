<?php

namespace App\Filament\Resources\CrushingProductions\Pages;

use App\Filament\Resources\CrushingProductions\CrushingProductionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrushingProductions extends ListRecords
{
    protected static string $resource = CrushingProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
