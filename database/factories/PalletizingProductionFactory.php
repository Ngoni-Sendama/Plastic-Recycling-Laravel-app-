<?php

namespace Database\Factories;

use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PalletizingProduction>
 */
class PalletizingProductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chipsInput = fake()->randomFloat(3, 250, 20000);
        $pelletsOutput = fake()->randomFloat(3, $chipsInput * 0.75, $chipsInput * 0.98);
        $loss = $chipsInput - $pelletsOutput;

        return [
            'date' => fake()->date(),
            'batch_number' => fake()->unique()->numerify('PL-#####'),
            'palletizing_receipt_id' => PalletizingReceipt::factory(),
            'grn_reference' => fake()->optional()->numerify('PR-#####'),
            'chips_input_kg' => $chipsInput,
            'pellets_output_kg' => $pelletsOutput,
            'loss_kg' => $loss,
            'loss_percentage' => $loss / $chipsInput,
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
