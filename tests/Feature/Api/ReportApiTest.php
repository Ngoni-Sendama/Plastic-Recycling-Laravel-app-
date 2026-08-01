<?php

use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PelletSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reportAuth(): User
{
    return User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
}

test('report endpoints require authentication', function () {
    $this->getJson('/api/reports/stock')->assertUnauthorized();
    $this->getJson('/api/reports/production')->assertUnauthorized();
    $this->getJson('/api/reports/sales')->assertUnauthorized();
    $this->getJson('/api/reports/cash-reconciliation')->assertUnauthorized();
});

test('report endpoints reject invalid date filters with a 422', function () {
    $auth = reportAuth();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/stock?from=not-a-date')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('from');

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/sales?to=garbage')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('to');
});

test('the stock report endpoint returns the full shape', function () {
    $auth = reportAuth();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/stock')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'period' => ['from', 'to'],
                'totals' => [
                    'material_purchased_kg',
                    'chips_produced_kg',
                    'chips_dispatched_kg',
                    'chips_on_hand_kg',
                    'chips_received_kg',
                    'receiving_variance_kg',
                    'pellets_produced_kg',
                    'pellets_sold_kg',
                    'finished_stock_kg',
                ],
                'per_material',
            ],
        ]);
});

test('the production report endpoint returns the full shape', function () {
    $auth = reportAuth();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/production')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'period' => ['from', 'to'],
                'crushing' => ['input_kg', 'output_kg', 'loss_kg', 'loss_percentage'],
                'palletizing' => ['input_kg', 'output_kg', 'loss_kg', 'loss_percentage'],
                'per_material',
                'monthly',
            ],
        ]);
});

test('the sales report endpoint returns the full shape', function () {
    $auth = reportAuth();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/sales')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'period' => ['from', 'to'],
                'totals' => ['kg_sold', 'revenue', 'average_price_per_kg', 'transactions'],
                'per_customer',
                'monthly',
            ],
        ]);
});

test('the cash reconciliation report endpoint returns the full shape', function () {
    $auth = reportAuth();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/cash-reconciliation')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'period' => ['from', 'to'],
                'totals' => [
                    'sales_revenue',
                    'cash_remitted',
                    'balance_retained',
                    'cash_collection_gap',
                    'payable_to_crushing',
                    'outstanding_to_crushing',
                    'reconciliation_status',
                ],
                'remittances',
            ],
        ]);
});

test('report endpoints aggregate and filter data by date', function () {
    $auth = reportAuth();
    $material = Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    MaterialIntake::factory()->create([
        'material_id' => $material->id,
        'date' => '2026-07-20',
        'net_weight_kg' => 100,
        'total_value' => 40,
        'recorded_by_user_id' => $auth->id,
    ]);

    MaterialIntake::factory()->create([
        'material_id' => $material->id,
        'date' => '2026-08-05',
        'net_weight_kg' => 500,
        'total_value' => 200,
        'recorded_by_user_id' => $auth->id,
    ]);

    PelletSale::factory()->create([
        'date' => '2026-08-06',
        'customer_name' => 'Acme Plastics',
        'kg_sold' => 100,
        'unit_price' => 0.95,
        'amount_received' => 95,
        'recorded_by_user_id' => $auth->id,
    ]);

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/stock?from=2026-08-01&to=2026-08-31')
        ->assertOk()
        ->assertJsonPath('data.totals.material_purchased_kg', 500)
        ->assertJsonPath('data.period.from', '2026-08-01')
        ->assertJsonPath('data.period.to', '2026-08-31');

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/reports/sales')
        ->assertOk()
        ->assertJsonPath('data.totals.kg_sold', 100)
        ->assertJsonPath('data.per_customer.0.customer_name', 'Acme Plastics');
});
