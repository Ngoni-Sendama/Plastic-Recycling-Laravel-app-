<?php

namespace Database\Factories;

use App\Models\SyncConflict;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncConflict>
 */
class SyncConflictFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_name' => 'pellet_sales',
            'record_id' => 1,
            'local_id' => fake()->uuid(),
            'submitted_by_user_id' => User::factory(),
            'server_version' => 1,
            'submitted_version' => 1,
            'server_payload' => [],
            'submitted_payload' => [],
            'changed_fields' => [],
            'status' => 'pending',
        ];
    }
}
