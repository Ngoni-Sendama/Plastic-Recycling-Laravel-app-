<?php

namespace Database\Factories;

use App\Models\MaterialIntake;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialIntake>
 */
class MaterialIntakeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grossWeight = fake()->randomFloat(3, 500, 30000);
        $tareWeight = fake()->randomFloat(3, 50, 1500);
        $netWeight = $grossWeight - $tareWeight;
        $unitPrice = fake()->randomFloat(2, 0.25, 2.5);

        return [
            'date' => fake()->date(),
            'grn_number' => fake()->unique()->numerify('GRN-#####'),
            'buyer_name' => fake()->company(),
            'material_id' => Material::factory(),
            'gross_weight_kg' => $grossWeight,
            'tare_weight_kg' => $tareWeight,
            'net_weight_kg' => $netWeight,
            'unit_price' => $unitPrice,
            'total_value' => $netWeight * $unitPrice,
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
