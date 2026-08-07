<?php

use App\Models\Buyer;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the material intake qz print page for authenticated users', function (): void {
    $user = User::factory()->create();
    $buyer = Buyer::factory()->create();
    $material = Material::factory()->create();

    $intake = MaterialIntake::factory()->create([
        'buyer_id' => $buyer->id,
        'material_id' => $material->id,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('web.material-intakes.qz-print', $intake));

    $response->assertOk();
});
