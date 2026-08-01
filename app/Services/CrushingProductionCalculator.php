<?php

namespace App\Services;

class CrushingProductionCalculator
{
    /**
     * Loss is input weight minus output chips, never below zero.
     */
    public static function lossKg(float $inputWeightKg, float $outputChipsKg): float
    {
        return round(max($inputWeightKg - $outputChipsKg, 0), 3);
    }

    /**
     * Loss percentage is loss divided by input weight.
     */
    public static function lossPercentage(float $inputWeightKg, float $lossKg): float
    {
        return $inputWeightKg > 0 ? round($lossKg / $inputWeightKg, 4) : 0.0;
    }

    /**
     * Recalculate the derived fields for a crushing production record.
     *
     * @param  array<string, mixed>  $data
     * @return array{loss_kg: float, loss_percentage: float}
     */
    public static function calculate(array $data): array
    {
        $inputWeightKg = (float) ($data['input_weight_kg'] ?? 0);
        $outputChipsKg = (float) ($data['output_chips_kg'] ?? 0);

        $lossKg = self::lossKg($inputWeightKg, $outputChipsKg);

        return [
            'loss_kg' => $lossKg,
            'loss_percentage' => self::lossPercentage($inputWeightKg, $lossKg),
        ];
    }
}
