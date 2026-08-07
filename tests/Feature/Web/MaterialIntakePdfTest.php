<?php

use App\Models\Buyer;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('material intake pdf renders the CR-01 form layout', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create([
        'code' => 'PP',
        'name' => 'Polypropylene',
    ]);
    $buyer = Buyer::factory()->create([
        'buyer_name' => 'GreenCycle Suppliers',
        'contact_number' => '0771234567',
    ]);
    $intake = MaterialIntake::factory()->create([
        'date' => '2026-08-05',
        'grn_number' => 'GRN-2026-0001',
        'buyer_id' => $buyer->id,
        'buyer_name' => $buyer->buyer_name,
        'material_id' => $material->id,
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'net_weight_kg' => 1170,
        'unit_price' => 0.42,
        'total_value' => 491.4,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/material-intakes/'.$intake->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('material-intake-GRN-2026-0001.pdf');
});
