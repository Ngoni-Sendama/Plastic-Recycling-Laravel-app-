<?php

namespace App\Filament\Resources\PalletizingReceipts\Pages;

use App\Filament\Resources\PalletizingReceipts\PalletizingReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPalletizingReceipt extends EditRecord
{
    protected static string $resource = PalletizingReceiptResource::class;

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
