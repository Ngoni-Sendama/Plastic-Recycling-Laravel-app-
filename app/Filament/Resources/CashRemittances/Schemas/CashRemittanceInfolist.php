<?php

namespace App\Filament\Resources\CashRemittances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CashRemittanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('voucher_number'),
                TextEntry::make('period_covered')
                    ->placeholder('-'),
                TextEntry::make('chips_delivered_kg')
                    ->numeric(),
                TextEntry::make('recovery_price_per_kg')
                    ->numeric(),
                TextEntry::make('sales_revenue')
                    ->numeric(),
                TextEntry::make('cash_remitted')
                    ->numeric(),
                TextEntry::make('max_remittance_due')
                    ->numeric(),
                TextEntry::make('balance_retained')
                    ->numeric(),
                TextEntry::make('recordedByUser.name')
                    ->label('Recorded by user')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
