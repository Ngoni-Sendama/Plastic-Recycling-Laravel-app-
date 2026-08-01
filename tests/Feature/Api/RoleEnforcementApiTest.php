<?php

use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a crusher operator can create crushing productions', function () {
    $user = apiUser('Crusher operator', ['username' => 'crusher01']);
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/crushing-productions', [
            'date' => '2026-07-31',
            'batch_number' => 'CR-BATCH-0001',
            'grn_reference' => 'GRN-2026-0001',
            'material_code' => 'PP',
            'input_weight_kg' => 1170,
            'output_chips_kg' => 1098.5,
        ])
        ->assertCreated();
});

test('a crusher operator cannot create users', function () {
    $user = apiUser('Crusher operator', ['username' => 'crusher01']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Intruder',
            'username' => 'intruder',
            'password' => 'secret123',
            'role' => 'Admin',
        ])
        ->assertForbidden();
});

test('a crusher operator cannot create material intakes', function () {
    $user = apiUser('Crusher operator', ['username' => 'crusher01']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/material-intakes', [
            'date' => '2026-07-31',
            'grn_number' => 'GRN-2026-0001',
            'buyer_name' => 'GreenCycle Suppliers',
            'material_code' => 'PP',
            'gross_weight_kg' => 1250,
            'tare_weight_kg' => 80,
            'unit_price' => 0.42,
        ])
        ->assertForbidden();
});

test('a stock receiver can create palletizing receipts but not crushing records', function () {
    $user = apiUser('Stock receiver', ['username' => 'receiver01']);
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/palletizing-receipts', [
            'date' => '2026-08-01',
            'grn_number' => 'PGRN-2026-0001',
            'dispatch_reference' => 'DN-2026-0001',
            'material_code' => 'PP',
            'weight_received_kg' => 1087.5,
            'rate_per_kg' => 0.18,
        ])
        ->assertCreated();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/crushing-productions', [
            'date' => '2026-07-31',
            'batch_number' => 'CR-BATCH-0001',
            'grn_reference' => 'GRN-2026-0001',
            'material_code' => 'PP',
            'input_weight_kg' => 1170,
            'output_chips_kg' => 1098.5,
        ])
        ->assertForbidden();
});

test('a palletizing operator can create palletizing productions but not users', function () {
    $user = apiUser('Palletizing operator', ['username' => 'palletizing01']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/palletizing-productions', [
            'date' => '2026-08-01',
            'batch_number' => 'PL-BATCH-0001',
            'grn_reference' => 'PGRN-2026-0001',
            'chips_input_kg' => 1087.5,
            'pellets_output_kg' => 1018.2,
        ])
        ->assertCreated();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Intruder',
            'username' => 'intruder',
            'password' => 'secret123',
            'role' => 'Admin',
        ])
        ->assertForbidden();
});

test('a supervisor can view reports but cannot modify records', function () {
    $user = apiUser('Supervisor', ['username' => 'supervisor01']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/reports/stock')
        ->assertOk();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/cash-remittances', [
            'date' => '2026-08-03',
            'voucher_number' => 'REM-2026-0001',
            'period_covered' => '2026-07-31 to 2026-08-02',
            'chips_delivered_kg' => 1087.5,
            'recovery_price_per_kg' => 0.18,
            'sales_revenue' => 608,
            'cash_remitted' => 500,
        ])
        ->assertForbidden();
});

test('a stock controller can create material intakes', function () {
    $user = apiUser('Stock controller', ['username' => 'stock01']);
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/material-intakes', [
            'date' => '2026-07-31',
            'grn_number' => 'GRN-2026-0001',
            'buyer_name' => 'GreenCycle Suppliers',
            'material_code' => 'PP',
            'gross_weight_kg' => 1250,
            'tare_weight_kg' => 80,
            'unit_price' => 0.42,
        ])
        ->assertCreated();
});

test('an admin can manage users and materials', function () {
    $user = apiUser('Admin', ['username' => 'admin']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'New Staff',
            'username' => 'staff01',
            'password' => 'secret123',
            'role' => 'Supervisor',
        ])
        ->assertCreated();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/materials', [
            'code' => 'PP',
            'name' => 'Polypropylene',
        ])
        ->assertCreated();
});
