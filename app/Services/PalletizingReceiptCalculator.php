<?php

namespace App\Services;

class PalletizingReceiptCalculator
{
    /**
     * Amount payable is received weight multiplied by the rate per kilogram.
     */
    public static function amountPayable(float $weightReceivedKg, float $ratePerKg): float
    {
        return round($weightReceivedKg * $ratePerKg, 2);
    }

    /**
     * Recalculate the derived fields for a palletizing receipt record.
     *
     * @param  array<string, mixed>  $data
     * @return array{amount_payable: float}
     */
    public static function calculate(array $data): array
    {
        $weightReceivedKg = (float) ($data['weight_received_kg'] ?? 0);
        $ratePerKg = (float) ($data['rate_per_kg'] ?? 0);

        return [
            'amount_payable' => self::amountPayable($weightReceivedKg, $ratePerKg),
        ];
    }
}
