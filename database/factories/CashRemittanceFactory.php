<?php

namespace Database\Factories;

use App\Models\CashRemittance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashRemittance>
 */
class CashRemittanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chipsDelivered = fake()->randomFloat(3, 500, 25000);
        $recoveryPrice = fake()->randomFloat(2, 0.1, 1.5);
        $salesRevenue = fake()->randomFloat(2, 1000, 50000);
        $maxRemittanceDue = $chipsDelivered * $recoveryPrice;
        $cashRemitted = fake()->randomFloat(2, 0, $maxRemittanceDue);

        return [
            'date' => fake()->date(),
            'voucher_number' => fake()->unique()->numerify('VCH-#####'),
            'period_covered' => fake()->optional()->monthName().' '.fake()->year(),
            'chips_delivered_kg' => $chipsDelivered,
            'recovery_price_per_kg' => $recoveryPrice,
            'sales_revenue' => $salesRevenue,
            'cash_remitted' => $cashRemitted,
            'max_remittance_due' => $maxRemittanceDue,
            'balance_retained' => $maxRemittanceDue - $cashRemitted,
            'recorded_by_user_id' => User::factory(),
        ];
    }
}
