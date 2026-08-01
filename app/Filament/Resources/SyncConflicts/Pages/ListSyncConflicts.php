<?php

namespace App\Filament\Resources\SyncConflicts\Pages;

use App\Filament\Resources\SyncConflicts\SyncConflictResource;
use Filament\Resources\Pages\ListRecords;

class ListSyncConflicts extends ListRecords
{
    protected static string $resource = SyncConflictResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
