<?php

use App\Models\CashRemittance;
use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PalletizingProduction;
use App\Models\PalletizingReceipt;
use App\Models\PelletSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dashboardAuth(): User
{
    return User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
}

test('the dashboard endpoint returns the full summary shape', function () {
    $auth = dashboardAuth();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'material_purchased_kg',
                'chips_produced_kg',
                'crushing_loss_percentage',
                'chips_dispatched_kg',
                'chips_on_hand_kg',
                'chips_received_kg',
                'receiving_variance_kg',
                'payable_to_crushing',
                'pellets_produced_kg',
                'palletizing_loss_percentage',
                'pellets_sold_kg',
                'finished_stock_kg',
                'sales_revenue',
                'cash_remitted',
                'balance_retained',
                'cash_collection_gap',
                'outstanding_to_crushing',
                'reconciliation_status',
            ],
        ]);
});

test('the dashboard endpoint aggregates the workflow data', function () {
    $auth = dashboardAuth();
    $material = Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $intake = MaterialIntake::factory()->create([
        'material_id' => $material->id,
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'net_weight_kg' => 1170,
        'unit_price' => 0.42,
        'total_value' => 491.4,
        'recorded_by_user_id' => $auth->id,
    ]);

    $crushing = CrushingProduction::factory()->create([
        'material_intake_id' => $intake->id,
        'material_id' => $material->id,
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
        'loss_kg' => 71.5,
        'loss_percentage' => 0.0611,
        'recorded_by_user_id' => $auth->id,
    ]);

    $dispatch = Dispatch::factory()->create([
        'crushing_production_id' => $crushing->id,
        'material_id' => $material->id,
        'weight_dispatched_kg' => 1090,
        'recorded_by_user_id' => $auth->id,
    ]);

    $receipt = PalletizingReceipt::factory()->create([
        'dispatch_id' => $dispatch->id,
        'material_id' => $material->id,
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
        'amount_payable' => 195.75,
        'recorded_by_user_id' => $auth->id,
    ]);

    PalletizingProduction::factory()->create([
        'palletizing_receipt_id' => $receipt->id,
        'chips_input_kg' => 1087.5,
        'pellets_output_kg' => 1018.2,
        'loss_kg' => 69.3,
        'loss_percentage' => 0.0637,
        'recorded_by_user_id' => $auth->id,
    ]);

    PelletSale::factory()->create([
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $auth->id,
    ]);

    CashRemittance::factory()->create([
        'cash_remitted' => 500,
        'balance_retained' => 108,
        'recorded_by_user_id' => $auth->id,
    ]);

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.material_purchased_kg', 1170)
        ->assertJsonPath('data.chips_produced_kg', 1098.5)
        ->assertJsonPath('data.chips_on_hand_kg', 8.5)
        ->assertJsonPath('data.payable_to_crushing', 195.75)
        ->assertJsonPath('data.pellets_produced_kg', 1018.2)
        ->assertJsonPath('data.sales_revenue', 608)
        ->assertJsonPath('data.cash_remitted', 500)
        ->assertJsonPath('data.balance_retained', 108)
        ->assertJsonPath('data.cash_collection_gap', 108)
        ->assertJsonPath('data.reconciliation_status', 'balanced');
});

test('the dashboard endpoint filters by date range', function () {
    $auth = dashboardAuth();
    $material = Material::factory()->create(['code' => 'PP']);

    MaterialIntake::factory()->create([
        'material_id' => $material->id,
        'date' => '2026-07-01',
        'net_weight_kg' => 1000,
        'total_value' => 400,
        'recorded_by_user_id' => $auth->id,
    ]);

    MaterialIntake::factory()->create([
        'material_id' => $material->id,
        'date' => '2026-07-20',
        'net_weight_kg' => 500,
        'total_value' => 200,
        'recorded_by_user_id' => $auth->id,
    ]);

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/dashboard?from=2026-07-10&to=2026-07-31')
        ->assertOk()
        ->assertJsonPath('data.material_purchased_kg', 500);
});
