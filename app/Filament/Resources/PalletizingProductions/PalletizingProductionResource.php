<?php

namespace App\Filament\Resources\PalletizingProductions;

use App\Filament\Resources\PalletizingProductions\Pages\CreatePalletizingProduction;
use App\Filament\Resources\PalletizingProductions\Pages\EditPalletizingProduction;
use App\Filament\Resources\PalletizingProductions\Pages\ListPalletizingProductions;
use App\Filament\Resources\PalletizingProductions\Pages\ViewPalletizingProduction;
use App\Filament\Resources\PalletizingProductions\Schemas\PalletizingProductionForm;
use App\Filament\Resources\PalletizingProductions\Schemas\PalletizingProductionInfolist;
use App\Filament\Resources\PalletizingProductions\Tables\PalletizingProductionsTable;
use App\Models\PalletizingProduction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PalletizingProductionResource extends Resource
{
    protected static ?string $model = PalletizingProduction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Palletizing Site';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'batch_number';

    public static function form(Schema $schema): Schema
    {
        return PalletizingProductionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PalletizingProductionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PalletizingProductionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPalletizingProductions::route('/'),
            'create' => CreatePalletizingProduction::route('/create'),
            'view' => ViewPalletizingProduction::route('/{record}'),
            'edit' => EditPalletizingProduction::route('/{record}/edit'),
        ];
    }
}
