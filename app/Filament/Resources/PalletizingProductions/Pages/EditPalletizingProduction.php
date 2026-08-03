<?php

namespace App\Filament\Resources\PalletizingProductions\Pages;

use App\Filament\Resources\PalletizingProductions\PalletizingProductionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPalletizingProduction extends EditRecord
{
    protected static string $resource = PalletizingProductionResource::class;

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
