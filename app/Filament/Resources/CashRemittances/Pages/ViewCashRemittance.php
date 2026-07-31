<?php

namespace App\Filament\Resources\CashRemittances\Pages;

use App\Filament\Resources\CashRemittances\CashRemittanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRemittance extends ViewRecord
{
    protected static string $resource = CashRemittanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
