<?php

namespace Database\Seeders;

use App\Models\Buyer;
use App\Models\MaterialIntake;
use Illuminate\Database\Seeder;

class BuyerSeeder extends Seeder
{
    public function run(): void
    {
        $buyers = [
            ['buyer_name' => 'GreenCycle Suppliers', 'contact_number' => '+263772101234'],
            ['buyer_name' => 'Metro Plastics Harare', 'contact_number' => '+263773202345'],
            ['buyer_name' => 'EcoScrap Zimbabwe', 'contact_number' => '+263783303456'],
            ['buyer_name' => 'PlastiRecycle Pvt Ltd', 'contact_number' => '+263774404567'],
            ['buyer_name' => 'ZimWaste Solutions', 'contact_number' => '+263775505678'],
            ['buyer_name' => 'CleanHarvest Plastics', 'contact_number' => '+263776606789'],
            ['buyer_name' => 'PolyPack Industries', 'contact_number' => '+263777707890'],
            ['buyer_name' => 'SunPlastic Traders', 'contact_number' => '+263778808901'],
            ['buyer_name' => 'Africa Recycling Co', 'contact_number' => '+263779909012'],
            ['buyer_name' => 'Harare Plastics Hub', 'contact_number' => '+263780110123'],
            ['buyer_name' => 'Bulawayo Waste Management', 'contact_number' => '+263781211234'],
            ['buyer_name' => 'Chitungwiza Recyclers', 'contact_number' => '+263782312345'],
            ['buyer_name' => 'Mutare Green Solutions', 'contact_number' => '+263783413456'],
            ['buyer_name' => 'Gweru Plastic Exchange', 'contact_number' => '+263784514567'],
            ['buyer_name' => 'Masvingo Eco Traders', 'contact_number' => '+263785615678'],
            ['buyer_name' => 'Victoria Falls Recyclers', 'contact_number' => '+263786716789'],
            ['buyer_name' => 'Zvishavane Waste Co', 'contact_number' => '+263787817890'],
            ['buyer_name' => 'Kwekwe Plastics PTY', 'contact_number' => '+263788918901'],
        ];

        foreach ($buyers as $buyer) {
            Buyer::firstOrCreate(
                ['buyer_name' => $buyer['buyer_name']],
                $buyer
            );
        }

        // Link existing material intakes to buyers by name
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
