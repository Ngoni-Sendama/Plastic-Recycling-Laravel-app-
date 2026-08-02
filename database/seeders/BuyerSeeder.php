<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\MaterialIntake;
use Illuminate\Database\Seeder;

class BuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['buyer_name' => 'GreenCycle Suppliers', 'contact_number' => '0770000001'],
            ['buyer_name' => 'Metro Plastics', 'contact_number' => '0770000002'],
        ];

        foreach ($defaults as $default) {
            Buyer::firstOrCreate(['buyer_name' => $default['buyer_name']], $default);
        }

        $existingBuyerNames = MaterialIntake::query()
            ->select('buyer_name')
            ->distinct()
            ->orderBy('buyer_name')
            ->pluck('buyer_name')
            ->filter()
            ->values();

        foreach ($existingBuyerNames as $buyerName) {
            Buyer::firstOrCreate(
                ['buyer_name' => $buyerName],
                ['contact_number' => null]
            );
        }

        MaterialIntake::query()
            ->whereNull('buyer_id')
            ->get()
            ->each(function (MaterialIntake $materialIntake): void {
                $buyer = Buyer::where('buyer_name', $materialIntake->buyer_name)->first();

                if ($buyer) {
                    $materialIntake->update(['buyer_id' => $buyer->id]);
                }
            });
    }
}
