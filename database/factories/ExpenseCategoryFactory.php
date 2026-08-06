<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Rent',
                'Transport',
                'Fuel',
                'Wages',
                'Repairs',
                'Stationery',
                'Miscellaneous',
            ]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
