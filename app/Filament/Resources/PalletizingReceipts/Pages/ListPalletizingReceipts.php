<?php

namespace App\Filament\Resources\PalletizingReceipts\Pages;

use App\Filament\Resources\PalletizingReceipts\PalletizingReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPalletizingReceipts extends ListRecords
{
    protected static string $resource = PalletizingReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
