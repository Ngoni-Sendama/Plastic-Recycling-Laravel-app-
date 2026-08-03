<?php

namespace App\Filament\Resources\PalletizingReceipts;

use App\Filament\Resources\PalletizingReceipts\Pages\CreatePalletizingReceipt;
use App\Filament\Resources\PalletizingReceipts\Pages\EditPalletizingReceipt;
use App\Filament\Resources\PalletizingReceipts\Pages\ListPalletizingReceipts;
use App\Filament\Resources\PalletizingReceipts\Pages\ViewPalletizingReceipt;
use App\Filament\Resources\PalletizingReceipts\Schemas\PalletizingReceiptForm;
use App\Filament\Resources\PalletizingReceipts\Schemas\PalletizingReceiptInfolist;
use App\Filament\Resources\PalletizingReceipts\Tables\PalletizingReceiptsTable;
use App\Models\PalletizingReceipt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PalletizingReceiptResource extends Resource
{
    protected static ?string $model = PalletizingReceipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = 'Palletizing Site';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'grn_number';

    public static function resolveRecordRouteBinding($key, $parameters, $route)
    {
        return static::getModel()::withTrashed()->find($parameters[$key]);
    }

    public static function form(Schema $schema): Schema
    {
        return PalletizingReceiptForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PalletizingReceiptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PalletizingReceiptsTable::configure($table);
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
            'index' => ListPalletizingReceipts::route('/'),
            'create' => CreatePalletizingReceipt::route('/create'),
            'view' => ViewPalletizingReceipt::route('/{record}'),
            'edit' => EditPalletizingReceipt::route('/{record}/edit'),
        ];
    }
}
