<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_number' => 'EXP-'.now()->format('Y').'-'.fake()->unique()->numberBetween(1, 9999),
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'expense_category_id' => ExpenseCategory::factory(),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 5, 2500),
            'payment_method' => fake()->randomElement(['Cash', 'Bank Transfer', 'EcoCash', 'Card']),
            'recorded_by_user_id' => null,
            'lock_version' => 1,
        ];
    }
}
