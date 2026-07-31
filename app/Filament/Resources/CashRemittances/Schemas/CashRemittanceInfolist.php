<?php

namespace App\Filament\Resources\CashRemittances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashRemittanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voucher Details')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('voucher_number')->label('Voucher Number'),
                        TextEntry::make('period_covered')->placeholder('-'),
                    ])
                    ->columns(3),
                Section::make('Cash Reconciliation')
                    ->schema([
                        TextEntry::make('chips_delivered_kg')->numeric(),
                        TextEntry::make('recovery_price_per_kg')->numeric(),
                        TextEntry::make('max_remittance_due')->numeric(),
                        TextEntry::make('sales_revenue')->numeric(),
                        TextEntry::make('cash_remitted')->numeric(),
                        TextEntry::make('balance_retained')->numeric(),
                    ])
                    ->columns(3),
                Section::make('Audit')
                    ->schema([
                        TextEntry::make('recordedByUser.name')->label('Recorded by')->placeholder('-'),
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
