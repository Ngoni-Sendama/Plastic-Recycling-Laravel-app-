<?php

namespace App\Filament\Resources\MaterialIntakes\Pages;

use App\Filament\Resources\MaterialIntakes\MaterialIntakeResource;
use App\Models\Buyer;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMaterialIntake extends EditRecord
{
    protected static string $resource = MaterialIntakeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! blank($data['buyer_id'] ?? null)) {
            $data['buyer_name'] = Buyer::query()->findOrFail($data['buyer_id'])->buyer_name;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
