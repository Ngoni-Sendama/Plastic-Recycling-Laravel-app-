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
use App\Services\DashboardSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('summary is all zeros when there are no records', function () {
    $summary = app(DashboardSummaryService::class)->summary();

    expect($summary['material_purchased_kg'])->toBe(0.0);
    expect($summary['chips_produced_kg'])->toBe(0.0);
    expect($summary['chips_dispatched_kg'])->toBe(0.0);
    expect($summary['chips_on_hand_kg'])->toBe(0.0);
    expect($summary['chips_received_kg'])->toBe(0.0);
    expect($summary['receiving_variance_kg'])->toBe(0.0);
    expect($summary['payable_to_crushing'])->toBe(0.0);
    expect($summary['pellets_produced_kg'])->toBe(0.0);
    expect($summary['finished_stock_kg'])->toBe(0.0);
    expect($summary['sales_revenue'])->toBe(0.0);
    expect($summary['cash_remitted'])->toBe(0.0);
    expect($summary['balance_retained'])->toBe(0.0);
    expect($summary['reconciliation_status'])->toBe('balanced');
});

test('summary aggregates the full workflow', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $intake = MaterialIntake::factory()->create([
        'date' => '2026-07-31',
        'material_id' => $material->id,
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'net_weight_kg' => 1170,
        'unit_price' => 0.42,
        'total_value' => 491.4,
        'recorded_by_user_id' => $user->id,
    ]);

    $crushing = CrushingProduction::factory()->create([
        'date' => '2026-07-31',
        'material_intake_id' => $intake->id,
        'material_id' => $material->id,
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
        'loss_kg' => 71.5,
        'loss_percentage' => 0.0611,
        'recorded_by_user_id' => $user->id,
    ]);

    $dispatch = Dispatch::factory()->create([
        'date' => '2026-07-31',
        'crushing_production_id' => $crushing->id,
        'material_id' => $material->id,
        'weight_dispatched_kg' => 1090,
        'recorded_by_user_id' => $user->id,
    ]);

    $receipt = PalletizingReceipt::factory()->create([
        'date' => '2026-08-01',
        'dispatch_id' => $dispatch->id,
        'material_id' => $material->id,
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
        'amount_payable' => 195.75,
        'recorded_by_user_id' => $user->id,
    ]);

    PalletizingProduction::factory()->create([
        'date' => '2026-08-01',
        'palletizing_receipt_id' => $receipt->id,
        'chips_input_kg' => 1087.5,
        'pellets_output_kg' => 1018.2,
        'loss_kg' => 69.3,
        'loss_percentage' => 0.0637,
        'recorded_by_user_id' => $user->id,
    ]);

    PelletSale::factory()->create([
        'date' => '2026-08-02',
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $user->id,
    ]);

    CashRemittance::factory()->create([
        'date' => '2026-08-03',
        'chips_delivered_kg' => 1087.5,
        'recovery_price_per_kg' => 0.18,
        'sales_revenue' => 608,
        'cash_remitted' => 500,
        'max_remittance_due' => 195.75,
        'balance_retained' => 108,
        'recorded_by_user_id' => $user->id,
    ]);

    $summary = app(DashboardSummaryService::class)->summary();

    expect($summary['material_purchased_kg'])->toBe(1170.0);
    expect($summary['chips_produced_kg'])->toBe(1098.5);
    expect($summary['crushing_loss_percentage'])->toBe(0.0611);
    expect($summary['chips_dispatched_kg'])->toBe(1090.0);
    expect($summary['chips_on_hand_kg'])->toEqualWithDelta(8.5, 0.001);
    expect($summary['chips_received_kg'])->toBe(1087.5);
    expect($summary['receiving_variance_kg'])->toEqualWithDelta(2.5, 0.001);
    expect($summary['payable_to_crushing'])->toBe(195.75);
    expect($summary['pellets_produced_kg'])->toBe(1018.2);
    expect($summary['palletizing_loss_percentage'])->toBe(0.0637);
    expect($summary['pellets_sold_kg'])->toBe(640.0);
    expect($summary['finished_stock_kg'])->toEqualWithDelta(378.2, 0.01);
    expect($summary['sales_revenue'])->toBe(608.0);
    expect($summary['cash_remitted'])->toBe(500.0);
    expect($summary['balance_retained'])->toBe(108.0);
    expect($summary['cash_collection_gap'])->toEqualWithDelta(108.0, 0.01);
    expect($summary['reconciliation_status'])->toBe('balanced');
});

test('summary respects the date range filters', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create();

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

    $summary = app(DashboardSummaryService::class)->summary($from, $to);

    expect($summary['material_purchased_kg'])->toBe(200.0);
});
