<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialIntakes extends ListRecords
{
    protected static string $resource = MaterialIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
