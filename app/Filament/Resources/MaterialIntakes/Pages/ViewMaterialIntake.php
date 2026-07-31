<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMaterialIntake extends ViewRecord
{
    protected static string $resource = MaterialIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
