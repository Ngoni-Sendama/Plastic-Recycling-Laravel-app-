<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->users() as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ],
            );
        }
    }

    /**
     * @return array<int, array{name: string, username: string, email: string, password: string, role: string}>
     */
    private function users(): array
    {
        return [
            [
                'name' => 'System Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'admin123',
                'role' => 'Admin',
            ],

        ];
    }
}
