<?php

use App\Services\PelletSaleCalculator;

test('amount received is kilograms sold multiplied by unit price', function () {
    expect(PelletSaleCalculator::amountReceived(640, 0.95))->toBe(608.0);
});

test('calculate returns amount received', function () {
    $values = PelletSaleCalculator::calculate([
        'kg_sold' => 640,
        'unit_price' => 0.95,
    ]);

    expect($values['amount_received'])->toBe(608.0);
});

test('calculate handles missing inputs as zero', function () {
    $values = PelletSaleCalculator::calculate([]);

    expect($values['amount_received'])->toBe(0.0);
});
