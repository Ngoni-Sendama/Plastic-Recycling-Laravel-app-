<?php

use App\Services\CashRemittanceCalculator;

test('max remittance due is chips delivered multiplied by recovery price', function () {
    expect(CashRemittanceCalculator::maxRemittanceDue(1087.5, 0.18))->toBe(195.75);
});

test('balance retained is sales revenue minus cash remitted', function () {
    expect(CashRemittanceCalculator::balanceRetained(608, 500))->toBe(108.0);
});

test('calculate returns max remittance due and balance retained', function () {
    $values = CashRemittanceCalculator::calculate([
        'chips_delivered_kg' => 1087.5,
        'recovery_price_per_kg' => 0.18,
        'sales_revenue' => 608,
        'cash_remitted' => 500,
    ]);

    expect($values['max_remittance_due'])->toBe(195.75);
    expect($values['balance_retained'])->toBe(108.0);
});

test('calculate handles missing inputs as zero', function () {
    $values = CashRemittanceCalculator::calculate([]);

    expect($values['max_remittance_due'])->toBe(0.0);
    expect($values['balance_retained'])->toBe(0.0);
});
