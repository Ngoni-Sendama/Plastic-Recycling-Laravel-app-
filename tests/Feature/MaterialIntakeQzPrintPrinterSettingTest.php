<?php

use App\Models\Buyer;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PrinterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('injects the authenticated user printer name into the qz print payload', function (): void {
    $user = User::factory()->create();
    PrinterSetting::query()->create([
        'user_id' => $user->id,
        'printer_name' => 'POS58 Printer',
    ]);

    $buyer = Buyer::factory()->create();
    $material = Material::factory()->create();

    $intake = MaterialIntake::factory()->create([
        'buyer_id' => $buyer->id,
        'material_id' => $material->id,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'web')->get(route('web.material-intakes.qz-print', $intake));

    $response->assertOk();
    $response->assertSee('POS58 Printer');
});
