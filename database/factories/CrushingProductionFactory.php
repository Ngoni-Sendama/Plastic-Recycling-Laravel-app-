<?php

namespace Database\Factories;

use App\Models\CrushingProduction;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrushingProduction>
 */
class CrushingProductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inputWeight = fake()->randomFloat(3, 500, 25000);
        $outputChips = fake()->randomFloat(3, $inputWeight * 0.75, $inputWeight * 0.98);
        $loss = $inputWeight - $outputChips;

        return [
            'date' => fake()->date(),
            'batch_number' => fake()->unique()->numerify('CR-#####'),
            'material_intake_id' => MaterialIntake::factory(),
            'grn_reference' => fake()->optional()->numerify('GRN-#####'),
            'material_id' => Material::factory(),
            'input_weight_kg' => $inputWeight,
            'output_chips_kg' => $outputChips,
            'loss_kg' => $loss,
            'loss_percentage' => $loss / $inputWeight,
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
