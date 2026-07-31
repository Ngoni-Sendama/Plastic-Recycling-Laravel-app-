<?php

namespace Database\Factories;

use App\Models\PalletizingReceipt;
use App\Models\Dispatch;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PalletizingReceipt>
 */
class PalletizingReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weightReceived = fake()->randomFloat(3, 250, 20000);
        $ratePerKg = fake()->randomFloat(2, 0.1, 1.5);

        return [
            'date' => fake()->date(),
            'grn_number' => fake()->unique()->numerify('PR-#####'),
            'dispatch_id' => Dispatch::factory(),
            'dispatch_reference' => fake()->optional()->numerify('DN-#####'),
            'material_id' => Material::factory(),
            'weight_received_kg' => $weightReceived,
            'rate_per_kg' => $ratePerKg,
            'amount_payable' => $weightReceived * $ratePerKg,
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
