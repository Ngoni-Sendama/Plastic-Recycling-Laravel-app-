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
use App\Services\ReportSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('stock summary is all zeros when there are no records', function () {
    $stock = app(ReportSummaryService::class)->stockSummary();

    expect($stock['totals']['material_purchased_kg'])->toBe(0.0);
    expect($stock['totals']['chips_produced_kg'])->toBe(0.0);
    expect($stock['totals']['chips_on_hand_kg'])->toBe(0.0);
    expect($stock['totals']['finished_stock_kg'])->toBe(0.0);
    expect($stock['per_material'])->toBe([]);
});

test('stock summary reports the material flow per material', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $intake = MaterialIntake::factory()->create([
        'date' => '2026-07-31',
        'material_id' => $material->id,
        'net_weight_kg' => 1170,
        'total_value' => 491.4,
        'recorded_by_user_id' => $user->id,
    ]);

    $crushing = CrushingProduction::factory()->create([
        'date' => '2026-07-31',
        'material_intake_id' => $intake->id,
        'material_id' => $material->id,
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
        'recorded_by_user_id' => $user->id,
    ]);

    Dispatch::factory()->create([
        'date' => '2026-07-31',
        'crushing_production_id' => $crushing->id,
        'material_id' => $material->id,
        'weight_dispatched_kg' => 1090,
        'recorded_by_user_id' => $user->id,
    ]);

    PalletizingReceipt::factory()->create([
        'date' => '2026-08-01',
        'dispatch_id' => null,
        'material_id' => $material->id,
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
        'amount_payable' => 195.75,
        'recorded_by_user_id' => $user->id,
    ]);

    $stock = app(ReportSummaryService::class)->stockSummary();

    expect($stock['totals']['material_purchased_kg'])->toBe(1170.0);
    expect($stock['totals']['chips_produced_kg'])->toBe(1098.5);
    expect($stock['totals']['chips_on_hand_kg'])->toEqualWithDelta(8.5, 0.001);
    expect($stock['totals']['chips_received_kg'])->toBe(1087.5);

    expect($stock['per_material'])->toHaveCount(1);
    expect($stock['per_material'][0]['material_code'])->toBe('PP');
    expect($stock['per_material'][0]['purchased_kg'])->toBe(1170.0);
    expect($stock['per_material'][0]['dispatched_kg'])->toBe(1090.0);
    expect($stock['per_material'][0]['on_hand_kg'])->toEqualWithDelta(8.5, 0.001);
});

test('production summary reports crushing and palletizing performance', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create(['code' => 'PP']);

    CrushingProduction::factory()->create([
        'date' => '2026-07-31',
        'material_intake_id' => null,
        'material_id' => $material->id,
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
        'loss_kg' => 71.5,
        'loss_percentage' => 0.0611,
        'recorded_by_user_id' => $user->id,
    ]);

    PalletizingProduction::factory()->create([
        'date' => '2026-08-01',
        'palletizing_receipt_id' => null,
        'chips_input_kg' => 1087.5,
        'pellets_output_kg' => 1018.2,
        'loss_kg' => 69.3,
        'loss_percentage' => 0.0637,
        'recorded_by_user_id' => $user->id,
    ]);

    $production = app(ReportSummaryService::class)->productionSummary();

    expect($production['crushing']['input_kg'])->toBe(1170.0);
    expect($production['crushing']['output_kg'])->toBe(1098.5);
    expect($production['crushing']['loss_kg'])->toEqualWithDelta(71.5, 0.001);
    expect($production['crushing']['loss_percentage'])->toEqualWithDelta(0.0611, 0.001);

    expect($production['palletizing']['input_kg'])->toBe(1087.5);
    expect($production['palletizing']['output_kg'])->toBe(1018.2);
    expect($production['palletizing']['loss_percentage'])->toEqualWithDelta(0.0637, 0.001);

    expect($production['per_material'])->toHaveCount(1);
    expect($production['per_material'][0]['material_code'])->toBe('PP');

    $months = collect($production['monthly'])->pluck('period');
    expect($months)->toContain('2026-07');
    expect($months)->toContain('2026-08');
});

test('sales summary reports totals, per-customer and monthly views', function () {
    $user = User::factory()->create();

    PelletSale::factory()->create([
        'date' => '2026-08-01',
        'customer_name' => 'Acme Plastics',
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $user->id,
    ]);

    PelletSale::factory()->create([
        'date' => '2026-08-05',
        'customer_name' => 'Acme Plastics',
        'kg_sold' => 360,
        'unit_price' => 0.95,
        'amount_received' => 342,
        'recorded_by_user_id' => $user->id,
    ]);

    PelletSale::factory()->create([
        'date' => '2026-08-10',
        'customer_name' => 'Zeta Packaging',
        'kg_sold' => 100,
        'unit_price' => 1.00,
        'amount_received' => 100,
        'recorded_by_user_id' => $user->id,
    ]);

    $sales = app(ReportSummaryService::class)->salesSummary();

    expect($sales['totals']['kg_sold'])->toBe(1100.0);
    expect($sales['totals']['revenue'])->toBe(1050.0);
    expect($sales['totals']['average_price_per_kg'])->toEqualWithDelta(0.95, 0.01);
    expect($sales['totals']['transactions'])->toBe(3);

    expect($sales['per_customer'])->toHaveCount(2);
    expect($sales['per_customer'][0]['customer_name'])->toBe('Acme Plastics');
    expect($sales['per_customer'][0]['kg_sold'])->toBe(1000.0);
    expect($sales['per_customer'][0]['revenue'])->toBe(950.0);

    expect($sales['monthly'])->toHaveCount(1);
    expect($sales['monthly'][0]['period'])->toBe('2026-08');
    expect($sales['monthly'][0]['revenue'])->toBe(1050.0);
});

test('cash reconciliation reports remittance versus revenue', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create(['code' => 'PP']);

    $intake = MaterialIntake::factory()->create([
        'material_id' => $material->id,
        'net_weight_kg' => 1170,
        'recorded_by_user_id' => $user->id,
    ]);

    $crushing = CrushingProduction::factory()->create([
        'material_intake_id' => $intake->id,
        'material_id' => $material->id,
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
        'recorded_by_user_id' => $user->id,
    ]);

    $dispatch = Dispatch::factory()->create([
        'crushing_production_id' => $crushing->id,
        'material_id' => $material->id,
        'weight_dispatched_kg' => 1090,
        'recorded_by_user_id' => $user->id,
    ]);

    $receipt = PalletizingReceipt::factory()->create([
        'dispatch_id' => $dispatch->id,
        'material_id' => $material->id,
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
        'amount_payable' => 195.75,
        'recorded_by_user_id' => $user->id,
    ]);

    PelletSale::factory()->create([
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $user->id,
    ]);

    CashRemittance::factory()->create([
        'voucher_number' => 'CR-001',
        'chips_delivered_kg' => 1087.5,
        'recovery_price_per_kg' => 0.18,
        'sales_revenue' => 608,
        'cash_remitted' => 500,
        'max_remittance_due' => 195.75,
        'balance_retained' => 108,
        'recorded_by_user_id' => $user->id,
    ]);

    $cash = app(ReportSummaryService::class)->cashReconciliation();

    expect($cash['totals']['sales_revenue'])->toBe(608.0);
    expect($cash['totals']['cash_remitted'])->toBe(500.0);
    expect($cash['totals']['balance_retained'])->toBe(108.0);
    expect($cash['totals']['cash_collection_gap'])->toEqualWithDelta(108.0, 0.01);
    expect($cash['totals']['payable_to_crushing'])->toBe(195.75);
    expect($cash['totals']['reconciliation_status'])->toBe('balanced');

    expect($cash['remittances'])->toHaveCount(1);
    expect($cash['remittances'][0]['voucher_number'])->toBe('CR-001');
    expect($cash['remittances'][0]['cash_remitted'])->toBe(500.0);
});

test('report summaries respect the date range filters', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create(['code' => 'PP']);

    MaterialIntake::factory()->create([
        'date' => '2026-07-31',
        'material_id' => $material->id,
        'net_weight_kg' => 100,
        'recorded_by_user_id' => $user->id,
    ]);

    MaterialIntake::factory()->create([
        'date' => '2026-08-15',
        'material_id' => $material->id,
        'net_weight_kg' => 200,
        'recorded_by_user_id' => $user->id,
    ]);

    $from = Carbon::parse('2026-08-01');
    $to = Carbon::parse('2026-08-31');

    $stock = app(ReportSummaryService::class)->stockSummary($from, $to);

    expect($stock['totals']['material_purchased_kg'])->toBe(200.0);
    expect($stock['period']['from'])->toBe('2026-08-01');
    expect($stock['period']['to'])->toBe('2026-08-31');
});
