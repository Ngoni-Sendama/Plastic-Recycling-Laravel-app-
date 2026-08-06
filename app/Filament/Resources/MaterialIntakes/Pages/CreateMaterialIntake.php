<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use App\Models\Buyer;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterialIntake extends CreateRecord
{
    protected static string $resource = MaterialIntakeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! blank($data['buyer_id'] ?? null)) {
            $data['buyer_name'] = Buyer::query()->findOrFail($data['buyer_id'])->buyer_name;
        }

        return $data;
    }
}
