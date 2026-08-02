<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function actingApiUser(): User
{
    return apiUser('Admin', ['username' => 'api-user']);
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
        'buyer_name' => 'GreenCycle Suppliers',
        'material_code' => 'PP',
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'unit_price' => 0.42,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.grn_number', 'GRN-2026-0001')
        ->assertJsonPath('data.net_weight_kg', 1170)
        ->assertJsonPath('data.total_value', 491.4)
        ->assertJsonPath('data.material_id', $material->id);

    $this->getJson('/api/material-intakes', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.grn_number', 'GRN-2026-0001');
});

test('material intakes can be updated with recomputed values', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);
    $material = Material::factory()->create(['code' => 'HD', 'name' => 'High Density Polyethylene']);

    $created = $this->postJson('/api/material-intakes', [
        'date' => '2026-07-31',
        'buyer_name' => 'GreenCycle Suppliers',
        'material_code' => 'PP',
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'unit_price' => 0.42,
    ], apiHeaders($user))->assertCreated();

    $this->patchJson('/api/material-intakes/'.$created->json('data.id'), [
        'date' => '2026-08-01',
        'buyer_name' => 'Updated Buyer',
        'material_code' => 'HD',
        'gross_weight_kg' => 1000,
        'tare_weight_kg' => 75,
        'unit_price' => 0.5,
    ], apiHeaders($user))
        ->assertOk()
        ->assertJsonPath('data.buyer_name', 'Updated Buyer')
        ->assertJsonPath('data.material_id', $material->id)
        ->assertJsonPath('data.net_weight_kg', 925)
        ->assertJsonPath('data.total_value', 462.5);
});

test('workflow records can be updated and deleted', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $crushing = $this->postJson('/api/crushing-productions', [
        'date' => '2026-07-31',
        'material_code' => 'PP',
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
    ], apiHeaders($user))->assertCreated()->json('data');

    $this->patchJson('/api/crushing-productions/'.$crushing['id'], [
        'date' => '2026-08-01',
        'material_code' => 'PP',
        'input_weight_kg' => 1200,
        'output_chips_kg' => 1100,
    ], apiHeaders($user))
        ->assertOk()
        ->assertJsonPath('data.loss_kg', 100)
        ->assertJsonPath('data.loss_percentage', 0.0833);

    $this->deleteJson('/api/crushing-productions/'.$crushing['id'], apiHeaders($user))
        ->assertOk();
});

test('crushing productions can be created with computed loss', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP']);

    $response = $this->postJson('/api/crushing-productions', [
        'date' => '2026-07-31',
        'grn_reference' => 'GRN-2026-0001',
        'material_code' => 'PP',
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.batch_number', 'CR-BATCH-2026-0001')
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

test('dispatch schema exposes dispatch note mapping for mobile sync', function () {
    $user = actingApiUser();

    $this->getJson('/api/form-schemas', apiHeaders($user))
        ->assertOk()
        ->assertJsonPath('modules.dispatch.apiMapping.toApi.dispatchNo', 'dispatch_note_number')
        ->assertJsonPath('modules.dispatch.apiMapping.fromApi.dispatchNo', 'dispatch_note_number');
});

test('palletizing receipts can be created with computed amount', function () {
    $user = actingApiUser();
    Material::factory()->create(['code' => 'PP']);

    $response = $this->postJson('/api/palletizing-receipts', [
        'date' => '2026-08-01',
        'dispatch_reference' => 'DN-2026-0001',
        'material_code' => 'PP',
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.grn_number', 'PGRN-2026-0001')
        ->assertJsonPath('data.amount_payable', 195.75);

    $this->getJson('/api/palletizing-receipts', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('palletizing productions can be created with computed loss', function () {
    $user = actingApiUser();

    $response = $this->postJson('/api/palletizing-productions', [
        'date' => '2026-08-01',
        'grn_reference' => 'PGRN-2026-0001',
        'chips_input_kg' => 1087.5,
        'pellets_output_kg' => 1018.2,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.batch_number', 'PL-BATCH-2026-0001')
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
        'customer_name' => 'Metro Plastics',
        'kg_sold' => 640,
        'unit_price' => 0.95,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.receipt_number', 'SALE-2026-0001')
        ->assertJsonPath('data.amount_received', 608);

    $this->getJson('/api/pellet-sales', apiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('cash remittances can be created with computed values', function () {
    $user = actingApiUser();

    $response = $this->postJson('/api/cash-remittances', [
        'date' => '2026-08-03',
        'period_covered' => '2026-07-31 to 2026-08-02',
        'chips_delivered_kg' => 1087.5,
        'recovery_price_per_kg' => 0.18,
        'sales_revenue' => 608,
        'cash_remitted' => 500,
    ], apiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.voucher_number', 'REM-2026-0001')
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
        'buyer_name' => 'GreenCycle Suppliers',
        'material_code' => 'XX',
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'unit_price' => 0.42,
    ], apiHeaders($user))->assertUnprocessable()
        ->assertJsonValidationErrors('material_code');
});
