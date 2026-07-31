<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Material::upsert([
            ['code' => 'PP', 'name' => 'Polypropylene'],
            ['code' => 'HD', 'name' => 'High-density polyethylene'],
            ['code' => 'LD', 'name' => 'Low-density polyethylene'],
        ], ['code'], ['name']);
    }
}
