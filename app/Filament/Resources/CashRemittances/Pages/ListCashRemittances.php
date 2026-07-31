<?php

namespace App\Filament\Resources\CashRemittances\Pages;

use App\Filament\Resources\CashRemittances\CashRemittanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashRemittances extends ListRecords
{
    protected static string $resource = CashRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
