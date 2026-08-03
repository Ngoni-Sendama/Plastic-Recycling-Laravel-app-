<?php

namespace App\Filament\Resources\CashRemittances;

use App\Filament\Resources\CashRemittances\Pages\CreateCashRemittance;
use App\Filament\Resources\CashRemittances\Pages\EditCashRemittance;
use App\Filament\Resources\CashRemittances\Pages\ListCashRemittances;
use App\Filament\Resources\CashRemittances\Pages\ViewCashRemittance;
use App\Filament\Resources\CashRemittances\Schemas\CashRemittanceForm;
use App\Filament\Resources\CashRemittances\Schemas\CashRemittanceInfolist;
use App\Filament\Resources\CashRemittances\Tables\CashRemittancesTable;
use App\Models\CashRemittance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CashRemittanceResource extends Resource
{
    protected static ?string $model = CashRemittance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Sales & Cash';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'voucher_number';

    public static function form(Schema $schema): Schema
    {
        return CashRemittanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CashRemittanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashRemittancesTable::configure($table);
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
            'index' => ListCashRemittances::route('/'),
            'create' => CreateCashRemittance::route('/create'),
            'view' => ViewCashRemittance::route('/{record}'),
            'edit' => EditCashRemittance::route('/{record}/edit'),
        ];
    }
}
