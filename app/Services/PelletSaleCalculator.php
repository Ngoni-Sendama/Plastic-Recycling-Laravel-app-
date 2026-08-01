<?php

namespace App\Services;

class PelletSaleCalculator
{
    /**
     * Amount received is kilograms sold multiplied by unit price.
     */
    public static function amountReceived(float $kgSold, float $unitPrice): float
    {
        return round($kgSold * $unitPrice, 2);
    }

    /**
     * Recalculate the derived fields for a pellet sale record.
     *
     * @param  array<string, mixed>  $data
     * @return array{amount_received: float}
     */
    public static function calculate(array $data): array
    {
        $kgSold = (float) ($data['kg_sold'] ?? 0);
        $unitPrice = (float) ($data['unit_price'] ?? 0);

        return [
            'amount_received' => self::amountReceived($kgSold, $unitPrice),
        ];
    }
}
