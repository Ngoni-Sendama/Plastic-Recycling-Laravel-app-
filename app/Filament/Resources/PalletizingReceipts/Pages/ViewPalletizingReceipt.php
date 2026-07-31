<?php

namespace App\Filament\Resources\PalletizingReceipts\Pages;

use App\Filament\Resources\PalletizingReceipts\PalletizingReceiptResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPalletizingReceipt extends ViewRecord
{
    protected static string $resource = PalletizingReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
