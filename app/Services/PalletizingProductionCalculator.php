<?php

namespace App\Services;

class PalletizingProductionCalculator
{
    /**
     * Loss is chips input minus pellets output, never below zero.
     */
    public static function lossKg(float $chipsInputKg, float $pelletsOutputKg): float
    {
        return round(max($chipsInputKg - $pelletsOutputKg, 0), 3);
    }

    /**
     * Loss percentage is loss divided by chips input.
     */
    public static function lossPercentage(float $chipsInputKg, float $lossKg): float
    {
        return $chipsInputKg > 0 ? round($lossKg / $chipsInputKg, 4) : 0.0;
    }

    /**
     * Recalculate the derived fields for a palletizing production record.
     *
     * @param  array<string, mixed>  $data
     * @return array{loss_kg: float, loss_percentage: float}
     */
    public static function calculate(array $data): array
    {
        $chipsInputKg = (float) ($data['chips_input_kg'] ?? 0);
        $pelletsOutputKg = (float) ($data['pellets_output_kg'] ?? 0);

        $lossKg = self::lossKg($chipsInputKg, $pelletsOutputKg);

        return [
            'loss_kg' => $lossKg,
            'loss_percentage' => self::lossPercentage($chipsInputKg, $lossKg),
        ];
    }
}
