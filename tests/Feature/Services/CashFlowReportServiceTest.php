<?php

use App\Models\CashRemittance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PelletSale;
use App\Models\User;
use App\Services\CashFlowReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('cash flow report is empty when there are no records', function () {
    $report = app(CashFlowReportService::class)->report();

    expect($report['totals']['cash_in'])->toBe(0.0);
    expect($report['totals']['cash_out'])->toBe(0.0);
    expect($report['totals']['sales_revenue'])->toBe(0.0);
    expect($report['totals']['cash_remitted'])->toBe(0.0);
    expect($report['totals']['expenses'])->toBe(0.0);
    expect($report['totals']['available_cash_balance'])->toBe(0.0);
    expect($report['totals']['transactions'])->toBe(0);
    expect($report['entries'])->toBe([]);
});

test('cash flow report includes pellet sales as cash in', function () {
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
        'customer_name' => 'Zeta Packaging',
        'kg_sold' => 360,
        'unit_price' => 1.00,
        'amount_received' => 360,
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    expect($report['totals']['cash_in'])->toBe(968.0);
    expect($report['totals']['sales_revenue'])->toBe(968.0);
    expect($report['totals']['transactions'])->toBe(2);
    expect($report['totals']['inflows'])->toBe(2);
    expect($report['entries'])->toHaveCount(2);

    $firstEntry = $report['entries'][0];
    expect($firstEntry['direction'])->toBe('in');
    expect($firstEntry['source_type'])->toBe('sale');
    expect($firstEntry['cash_in'])->toBe(608.0);
    expect($firstEntry['cash_out'])->toBe(0.0);
});

test('cash flow report includes cash remittances as cash out', function () {
    $user = User::factory()->create();

    CashRemittance::factory()->create([
        'date' => '2026-08-10',
        'voucher_number' => 'CR-001',
        'cash_remitted' => 500,
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    expect($report['totals']['cash_out'])->toBe(500.0);
    expect($report['totals']['cash_remitted'])->toBe(500.0);
    expect($report['totals']['outflows'])->toBe(1);

    $entry = $report['entries'][0];
    expect($entry['direction'])->toBe('out');
    expect($entry['source_type'])->toBe('remittance');
    expect($entry['cash_in'])->toBe(0.0);
    expect($entry['cash_out'])->toBe(500.0);
});

test('cash flow report includes expenses as cash out', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Transport']);

    Expense::factory()->create([
        'date' => '2026-08-12',
        'expense_category_id' => $category->id,
        'description' => 'Fuel for truck',
        'amount' => 120,
        'payment_method' => 'Cash',
        'recorded_by_user_id' => $user->id,
    ]);

    Expense::factory()->create([
        'date' => '2026-08-14',
        'expense_category_id' => $category->id,
        'description' => 'Delivery charges',
        'amount' => 80,
        'payment_method' => 'EcoCash',
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    expect($report['totals']['cash_out'])->toBe(200.0);
    expect($report['totals']['expenses'])->toBe(200.0);
    expect($report['totals']['outflows'])->toBe(2);

    $entries = collect($report['entries']);
    expect($entries->where('source_type', 'expense'))->toHaveCount(2);
});

test('cash flow report merges all entries sorted by date then rank then id', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Wages']);

    PelletSale::factory()->create([
        'date' => '2026-08-01',
        'amount_received' => 600,
        'recorded_by_user_id' => $user->id,
    ]);

    CashRemittance::factory()->create([
        'date' => '2026-08-05',
        'cash_remitted' => 300,
        'recorded_by_user_id' => $user->id,
    ]);

    Expense::factory()->create([
        'date' => '2026-08-03',
        'expense_category_id' => $category->id,
        'amount' => 100,
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    $entries = collect($report['entries']);
    expect($entries)->toHaveCount(3);

    $dates = $entries->pluck('date')->all();
    expect($dates)->toBe(['2026-08-01', '2026-08-03', '2026-08-05']);
});

test('cash flow report calculates running balance correctly', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Rent']);

    PelletSale::factory()->create([
        'date' => '2026-08-01',
        'amount_received' => 1000,
        'recorded_by_user_id' => $user->id,
    ]);

    CashRemittance::factory()->create([
        'date' => '2026-08-05',
        'cash_remitted' => 400,
        'recorded_by_user_id' => $user->id,
    ]);

    Expense::factory()->create([
        'date' => '2026-08-10',
        'expense_category_id' => $category->id,
        'amount' => 200,
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    $entries = collect($report['entries']);

    expect($entries[0]['balance'])->toBe(1000.0);
    expect($entries[1]['balance'])->toBe(600.0);
    expect($entries[2]['balance'])->toBe(400.0);

    expect($report['totals']['available_cash_balance'])->toBe(400.0);
});

test('cash flow report respects date range filters', function () {
    $user = User::factory()->create();

    PelletSale::factory()->create([
        'date' => '2026-07-15',
        'amount_received' => 500,
        'recorded_by_user_id' => $user->id,
    ]);

    PelletSale::factory()->create([
        'date' => '2026-08-10',
        'amount_received' => 800,
        'recorded_by_user_id' => $user->id,
    ]);

    $from = Carbon::parse('2026-08-01');
    $to = Carbon::parse('2026-08-31');

    $report = app(CashFlowReportService::class)->report($from, $to);

    expect($report['totals']['cash_in'])->toBe(800.0);
    expect($report['totals']['transactions'])->toBe(1);
    expect($report['period']['from'])->toBe('2026-08-01');
    expect($report['period']['to'])->toBe('2026-08-31');
});

test('cash flow report mixes sales remittances and expenses correctly', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create(['name' => 'Fuel']);

    PelletSale::factory()->create([
        'date' => '2026-08-01',
        'amount_received' => 2000,
        'recorded_by_user_id' => $user->id,
    ]);

    CashRemittance::factory()->create([
        'date' => '2026-08-05',
        'cash_remitted' => 800,
        'recorded_by_user_id' => $user->id,
    ]);

    Expense::factory()->create([
        'date' => '2026-08-08',
        'expense_category_id' => $category->id,
        'amount' => 300,
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    expect($report['totals']['cash_in'])->toBe(2000.0);
    expect($report['totals']['cash_out'])->toBe(1100.0);
    expect($report['totals']['sales_revenue'])->toBe(2000.0);
    expect($report['totals']['cash_remitted'])->toBe(800.0);
    expect($report['totals']['expenses'])->toBe(300.0);
    expect($report['totals']['available_cash_balance'])->toBe(900.0);
    expect($report['totals']['transactions'])->toBe(3);
    expect($report['totals']['inflows'])->toBe(1);
    expect($report['totals']['outflows'])->toBe(2);
});
