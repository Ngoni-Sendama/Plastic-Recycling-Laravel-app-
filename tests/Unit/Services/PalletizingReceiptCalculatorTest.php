<?php

use App\Services\PalletizingReceiptCalculator;

test('amount payable is received weight multiplied by rate', function () {
    expect(PalletizingReceiptCalculator::amountPayable(1087.5, 0.18))->toBe(195.75);
});

test('calculate returns amount payable', function () {
    $values = PalletizingReceiptCalculator::calculate([
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
    ]);

    expect($values['amount_payable'])->toBe(195.75);
});

test('calculate handles missing inputs as zero', function () {
    $values = PalletizingReceiptCalculator::calculate([]);

    expect($values['amount_payable'])->toBe(0.0);
});
