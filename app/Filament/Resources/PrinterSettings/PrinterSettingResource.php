<?php

namespace App\Filament\Resources\PrinterSettings;

use App\Filament\Resources\PrinterSettings\Pages\CreatePrinterSetting;
use App\Filament\Resources\PrinterSettings\Pages\ListPrinterSettings;
use App\Filament\Resources\PrinterSettings\Pages\ViewPrinterSetting;
use App\Filament\Resources\PrinterSettings\Schemas\PrinterSettingForm;
use App\Filament\Resources\PrinterSettings\Tables\PrinterSettingsTable;
use App\Models\PrinterSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PrinterSettingResource extends Resource
{
    protected static ?string $model = PrinterSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'printer_name';

    public static function form(Schema $schema): Schema
    {
        return PrinterSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrinterSettingsTable::configure($table);
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
            'index' => ListPrinterSettings::route('/'),
            'create' => CreatePrinterSetting::route('/create'),
            'view' => ViewPrinterSetting::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->when(
            Auth::check() && ! Auth::user()?->hasRole('super_admin'),
            fn (Builder $builder) => $builder->where('user_id', Auth::id()),
        );
    }
}
