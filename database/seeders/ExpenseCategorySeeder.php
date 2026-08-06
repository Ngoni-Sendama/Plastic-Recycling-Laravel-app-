<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Rent', 'description' => 'Warehouse, office, or site rental costs.'],
            ['name' => 'Transport', 'description' => 'Fuel, delivery, and transport charges.'],
            ['name' => 'Fuel', 'description' => 'Fuel for vehicles and machinery.'],
            ['name' => 'Wages', 'description' => 'Staff wages and casual labor.'],
            ['name' => 'Repairs', 'description' => 'Equipment and facility repairs.'],
            ['name' => 'Stationery', 'description' => 'Office and stationery supplies.'],
            ['name' => 'Miscellaneous', 'description' => 'Any approved small expense not covered above.'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                $category + ['is_active' => true]
            );
        }
    }
}
