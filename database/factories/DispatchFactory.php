<?php

namespace Database\Factories;

use App\Models\Dispatch;
use App\Models\CrushingProduction;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dispatch>
 */
class DispatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'dispatch_note_number' => fake()->unique()->numerify('DN-#####'),
            'crushing_production_id' => CrushingProduction::factory(),
            'batch_reference' => fake()->optional()->numerify('CR-#####'),
            'material_id' => Material::factory(),
            'weight_dispatched_kg' => fake()->randomFloat(3, 250, 20000),
            'transported_by' => fake()->optional()->name(),
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
