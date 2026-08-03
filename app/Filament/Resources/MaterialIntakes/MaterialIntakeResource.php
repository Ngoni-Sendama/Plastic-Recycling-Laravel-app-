<?php

namespace App\Filament\Resources\MaterialIntakes;

use App\Filament\Resources\MaterialIntakes\Pages\CreateMaterialIntake;
use App\Filament\Resources\MaterialIntakes\Pages\EditMaterialIntake;
use App\Filament\Resources\MaterialIntakes\Pages\ListMaterialIntakes;
use App\Filament\Resources\MaterialIntakes\Pages\ViewMaterialIntake;
use App\Filament\Resources\MaterialIntakes\Schemas\MaterialIntakeForm;
use App\Filament\Resources\MaterialIntakes\Schemas\MaterialIntakeInfolist;
use App\Filament\Resources\MaterialIntakes\Tables\MaterialIntakesTable;
use App\Models\MaterialIntake;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MaterialIntakeResource extends Resource
{
    protected static ?string $model = MaterialIntake::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Crushing Site';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'grn_number';

    public static function form(Schema $schema): Schema
    {
        return MaterialIntakeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MaterialIntakeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaterialIntakesTable::configure($table);
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
            'index' => ListMaterialIntakes::route('/'),
            'create' => CreateMaterialIntake::route('/create'),
            'view' => ViewMaterialIntake::route('/{record}'),
            'edit' => EditMaterialIntake::route('/{record}/edit'),
        ];
    }
}
