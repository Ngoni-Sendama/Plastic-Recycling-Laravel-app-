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
            [
                'name' => 'Tawanda Moyo',
                'username' => 'crusher01',
                'email' => 'crusher01@example.com',
                'password' => 'password123',
                'role' => 'Crusher operator',
            ],
            [
                'name' => 'Rudo Ndlovu',
                'username' => 'receiver01',
                'email' => 'receiver01@example.com',
                'password' => 'password123',
                'role' => 'Stock receiver',
            ],
            [
                'name' => 'Nyasha Dube',
                'username' => 'supervisor01',
                'email' => 'supervisor01@example.com',
                'password' => 'password123',
                'role' => 'Supervisor',
            ],
            [
                'name' => 'Takudzwa Moyo',
                'username' => 'stock01',
                'email' => 'stock01@example.com',
                'password' => 'password123',
                'role' => 'Stock controller',
            ],
            [
                'name' => 'Rumbidzai Chirwa',
                'username' => 'palletizing01',
                'email' => 'palletizing01@example.com',
                'password' => 'password123',
                'role' => 'Palletizing operator',
            ],
        ];
    }
}
