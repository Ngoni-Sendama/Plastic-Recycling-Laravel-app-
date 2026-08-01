<?php

namespace App\Services;

class CashRemittanceCalculator
{
    /**
     * Maximum remittance due is chips delivered multiplied by the recovery price per kilogram.
     */
    public static function maxRemittanceDue(float $chipsDeliveredKg, float $recoveryPricePerKg): float
    {
        return round($chipsDeliveredKg * $recoveryPricePerKg, 2);
    }

    /**
     * Balance retained is sales revenue minus cash remitted.
     */
    public static function balanceRetained(float $salesRevenue, float $cashRemitted): float
    {
        return round($salesRevenue - $cashRemitted, 2);
    }

    /**
     * Recalculate the derived fields for a cash remittance record.
     *
     * @param  array<string, mixed>  $data
     * @return array{max_remittance_due: float, balance_retained: float}
     */
    public static function calculate(array $data): array
    {
        $chipsDeliveredKg = (float) ($data['chips_delivered_kg'] ?? 0);
        $recoveryPricePerKg = (float) ($data['recovery_price_per_kg'] ?? 0);
        $salesRevenue = (float) ($data['sales_revenue'] ?? 0);
        $cashRemitted = (float) ($data['cash_remitted'] ?? 0);

        return [
            'max_remittance_due' => self::maxRemittanceDue($chipsDeliveredKg, $recoveryPricePerKg),
            'balance_retained' => self::balanceRetained($salesRevenue, $cashRemitted),
        ];
    }
}
