<?php

namespace App\Filament\Resources\PelletSales\Pages;

use App\Filament\Resources\PelletSales\PelletSaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPelletSales extends ListRecords
{
    protected static string $resource = PelletSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
