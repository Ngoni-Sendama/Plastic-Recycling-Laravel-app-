<?php

namespace App\Services;

class MaterialIntakeCalculator
{
    /**
     * Net weight is gross weight minus tare weight, never below zero.
     */
    public static function netWeightKg(float $grossWeightKg, float $tareWeightKg): float
    {
        return round(max($grossWeightKg - $tareWeightKg, 0), 3);
    }

    /**
     * Total value is net weight multiplied by unit price.
     */
    public static function totalValue(float $netWeightKg, float $unitPrice): float
    {
        return round($netWeightKg * $unitPrice, 2);
    }

    /**
     * Recalculate the derived fields for a material intake record.
     *
     * @param  array<string, mixed>  $data
     * @return array{net_weight_kg: float, total_value: float}
     */
    public static function calculate(array $data): array
    {
        $grossWeightKg = (float) ($data['gross_weight_kg'] ?? 0);
        $tareWeightKg = (float) ($data['tare_weight_kg'] ?? 0);
        $unitPrice = (float) ($data['unit_price'] ?? 0);

        $netWeightKg = self::netWeightKg($grossWeightKg, $tareWeightKg);

        return [
            'net_weight_kg' => $netWeightKg,
            'total_value' => self::totalValue($netWeightKg, $unitPrice),
        ];
    }
}
