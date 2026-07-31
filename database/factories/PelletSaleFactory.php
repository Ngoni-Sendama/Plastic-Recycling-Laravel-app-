<?php

namespace Database\Factories;

use App\Models\PelletSale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PelletSale>
 */
class PelletSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kgSold = fake()->randomFloat(3, 100, 15000);
        $unitPrice = fake()->randomFloat(2, 0.5, 3.5);

        return [
            'date' => fake()->date(),
            'receipt_number' => fake()->unique()->numerify('RCPT-#####'),
            'customer_name' => fake()->company(),
            'kg_sold' => $kgSold,
            'unit_price' => $unitPrice,
            'amount_received' => $kgSold * $unitPrice,
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
