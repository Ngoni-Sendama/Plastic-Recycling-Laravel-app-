<?php

namespace App\Filament\Resources\SyncConflicts;

use App\Filament\Resources\SyncConflicts\Pages\ListSyncConflicts;
use App\Filament\Resources\SyncConflicts\Pages\ViewSyncConflict;
use App\Filament\Resources\SyncConflicts\Schemas\SyncConflictInfolist;
use App\Filament\Resources\SyncConflicts\Tables\SyncConflictsTable;
use App\Models\SyncConflict;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SyncConflictResource extends Resource
{
    protected static ?string $model = SyncConflict::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Sync';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'table_name';

    protected static ?string $navigationLabel = 'Sync Conflicts';

    protected static ?string $pluralModelLabel = 'Sync Conflicts';

    protected static ?string $modelLabel = 'Sync Conflict';

    public static function infolist(Schema $schema): Schema
    {
        return SyncConflictInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SyncConflictsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSyncConflicts::route('/'),
            'view' => ViewSyncConflict::route('/{record}'),
        ];
    }
}
