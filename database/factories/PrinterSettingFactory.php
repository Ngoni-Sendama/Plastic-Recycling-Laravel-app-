<?php

namespace Database\Factories;

use App\Models\PrinterSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrinterSetting>
 */
class PrinterSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'printer_name' => fake()->randomElement(['POS58 Printer', 'EPSON TM-T88', 'Generic Thermal']),
        ];
    }
}
