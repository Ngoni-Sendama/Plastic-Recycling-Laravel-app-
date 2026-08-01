<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function actingApiUser(): User
{
    return User::factory()->create(['username' => 'api-user']);
}

function apiHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('mobile')->plainTextToken];
}

test('workflow endpoints require authentication', function () {
    $this->getJson('/api/material-intakes')->assertUnauthorized();
    $this->postJson('/api/material-intakes', [])->assertUnauthorized();
});

test('material intakes can be created with computed values', function () {
    $user = actingApiUser();
    $material = Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $response = $this->postJson('/api/material-intakes', [
        'date' => '2026-07-31',
        'grn_number' => 'GRN-2026-0001',
        'buyer_name' => 'GreenCycle Suppliers',
        'material_code' => 'PP',
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'unit_price' => 0.42,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.net_weight_kg', 1170)
        ->assertJsonPath('data.total_value', 491.4)
        ->assertJsonPath('data.material_id', $material->id);

    $this->getJson('/api/material-intakes', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.grn_number', 'GRN-2026-0001');
});

test('crushing productions can be created with computed loss', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP']);

    $response = $this->postJson('/api/crushing-productions', [
        'date' => '2026-07-31',
        'batch_number' => 'CR-BATCH-0001',
        'grn_reference' => 'GRN-2026-0001',
        'material_code' => 'PP',
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.loss_kg', 71.5)
        ->assertJsonPath('data.loss_percentage', 0.0611);

    $this->getJson('/api/crushing-productions', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('dispatches can be created', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP']);

    $response = $this->postJson('/api/dispatches', [
        'date' => '2026-07-31',
        'dispatch_note_number' => 'DN-2026-0001',
        'batch_reference' => 'CR-BATCH-0001',
        'material_code' => 'PP',
        'weight_dispatched_kg' => 1090,
        'transported_by' => 'Highglen Truck 1',
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.dispatch_note_number', 'DN-2026-0001');

    $this->getJson('/api/dispatches', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('palletizing receipts can be created with computed amount', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP']);

    $response = $this->postJson('/api/palletizing-receipts', [
        'date' => '2026-08-01',
        'grn_number' => 'PGRN-2026-0001',
        'dispatch_reference' => 'DN-2026-0001',
        'material_code' => 'PP',
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.amount_payable', 195.75);

    $this->getJson('/api/palletizing-receipts', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('palletizing productions can be created with computed loss', function () {
    $user = actingApiUser();

    $response = $this->postJson('/api/palletizing-productions', [
        'date' => '2026-08-01',
        'batch_number' => 'PL-BATCH-0001',
        'grn_reference' => 'PGRN-2026-0001',
        'chips_input_kg' => 1087.5,
        'pellets_output_kg' => 1018.2,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.loss_kg', 69.3)
        ->assertJsonPath('data.loss_percentage', 0.0637);

    $this->getJson('/api/palletizing-productions', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('pellet sales can be created with computed amount', function () {
    $user = actingApiUser();

    $response = $this->postJson('/api/pellet-sales', [
        'date' => '2026-08-02',
        'receipt_number' => 'SALE-2026-0001',
        'customer_name' => 'Metro Plastics',
        'kg_sold' => 640,
        'unit_price' => 0.95,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.amount_received', 608);

    $this->getJson('/api/pellet-sales', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('cash remittances can be created with computed values', function () {
    $user = actingApiUser();

    $response = $this->postJson('/api/cash-remittances', [
        'date' => '2026-08-03',
        'voucher_number' => 'REM-2026-0001',
        'period_covered' => '2026-07-31 to 2026-08-02',
        'chips_delivered_kg' => 1087.5,
        'recovery_price_per_kg' => 0.18,
        'sales_revenue' => 608,
        'cash_remitted' => 500,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.max_remittance_due', 195.75)
        ->assertJsonPath('data.balance_retained', 108);

    $this->getJson('/api/cash-remittances', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('workflow validation errors are returned in the documented shape', function () {
    $user = actingApiUser();

    $response = $this->postJson('/api/material-intakes', [], apiHeaders($user));

    $response->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors']);
});

test('an unknown material code is rejected', function () {
    $user = actingApiUser();

    $this->postJson('/api/material-intakes', [
        'date' => '2026-07-31',
        'grn_number' => 'GRN-2026-0001',
        'buyer_name' => 'GreenCycle Suppliers',
        'material_code' => 'XX',
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'unit_price' => 0.42,
    ], apiHeaders($user))->assertUnprocessable()
        ->assertJsonValidationErrors('material_code');
});
