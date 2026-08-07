<?php

use App\Models\CashRemittance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PelletSale;
use App\Models\User;
use App\Services\CashFlowReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds a cash flow report from existing records', function (): void {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    PelletSale::factory()->create([
        'date' => '2026-08-01',
        'amount_received' => 1500,
        'recorded_by_user_id' => $user->id,
    ]);

    CashRemittance::factory()->create([
        'date' => '2026-08-01',
        'cash_remitted' => 300,
        'recorded_by_user_id' => $user->id,
    ]);

    Expense::factory()->create([
        'date' => '2026-08-01',
        'expense_category_id' => $category->id,
        'amount' => 200,
        'recorded_by_user_id' => $user->id,
    ]);

    $report = app(CashFlowReportService::class)->report();

    expect($report['totals']['cash_in'])->toBe(1500.0)
        ->and($report['totals']['cash_out'])->toBe(500.0)
        ->and($report['totals']['available_cash_balance'])->toBe(1000.0)
        ->and($report['entries'])->toHaveCount(3)
        ->and($report['entries'][0]['source_url'])->toContain('/pellet-sales/')
        ->and($report['entries'][1]['source_url'])->toContain('/cash-remittances/')
        ->and($report['entries'][2]['source_url'])->toContain('/expenses/');
});
