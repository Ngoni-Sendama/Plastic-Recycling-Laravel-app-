<?php

namespace App\Filament\Resources\PelletSales\Pages;

use App\Filament\Resources\PelletSales\PelletSaleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPelletSale extends ViewRecord
{
    protected static string $resource = PelletSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
