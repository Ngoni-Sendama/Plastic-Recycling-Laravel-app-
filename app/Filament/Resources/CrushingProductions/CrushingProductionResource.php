<?php

namespace App\Filament\Resources\CrushingProductions;

use App\Filament\Resources\CrushingProductions\Pages\CreateCrushingProduction;
use App\Filament\Resources\CrushingProductions\Pages\EditCrushingProduction;
use App\Filament\Resources\CrushingProductions\Pages\ListCrushingProductions;
use App\Filament\Resources\CrushingProductions\Pages\ViewCrushingProduction;
use App\Filament\Resources\CrushingProductions\Schemas\CrushingProductionForm;
use App\Filament\Resources\CrushingProductions\Schemas\CrushingProductionInfolist;
use App\Filament\Resources\CrushingProductions\Tables\CrushingProductionsTable;
use App\Models\CrushingProduction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrushingProductionResource extends Resource
{
    protected static ?string $model = CrushingProduction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Crushing Site';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'batch_number';

    public static function form(Schema $schema): Schema
    {
        return CrushingProductionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CrushingProductionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrushingProductionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrushingProductions::route('/'),
            'create' => CreateCrushingProduction::route('/create'),
            'view' => ViewCrushingProduction::route('/{record}'),
            'edit' => EditCrushingProduction::route('/{record}/edit'),
        ];
    }
}
