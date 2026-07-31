<?php

namespace App\Filament\Resources\PelletSales;

use App\Filament\Resources\PelletSales\Pages\CreatePelletSale;
use App\Filament\Resources\PelletSales\Pages\EditPelletSale;
use App\Filament\Resources\PelletSales\Pages\ListPelletSales;
use App\Filament\Resources\PelletSales\Pages\ViewPelletSale;
use App\Filament\Resources\PelletSales\Schemas\PelletSaleForm;
use App\Filament\Resources\PelletSales\Schemas\PelletSaleInfolist;
use App\Filament\Resources\PelletSales\Tables\PelletSalesTable;
use App\Models\PelletSale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PelletSaleResource extends Resource
{
    protected static ?string $model = PelletSale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Sales & Cash';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public static function form(Schema $schema): Schema
    {
        return PelletSaleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PelletSaleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PelletSalesTable::configure($table);
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
            'index' => ListPelletSales::route('/'),
            'create' => CreatePelletSale::route('/create'),
            'view' => ViewPelletSale::route('/{record}'),
            'edit' => EditPelletSale::route('/{record}/edit'),
        ];
    }
}
