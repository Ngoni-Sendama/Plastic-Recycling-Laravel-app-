<?php

use App\Services\MaterialIntakeCalculator;

test('net weight is gross minus tare', function () {
    expect(MaterialIntakeCalculator::netWeightKg(1250, 80))->toBe(1170.0);
});

test('net weight never drops below zero', function () {
    expect(MaterialIntakeCalculator::netWeightKg(80, 1250))->toBe(0.0);
});

test('total value is net weight multiplied by unit price', function () {
    expect(MaterialIntakeCalculator::totalValue(1170, 0.42))->toBe(491.4);
});

test('calculate returns net weight and total value', function () {
    $values = MaterialIntakeCalculator::calculate([
        'gross_weight_kg' => 1250,
        'tare_weight_kg' => 80,
        'unit_price' => 0.42,
    ]);

    expect($values['net_weight_kg'])->toBe(1170.0);
    expect($values['total_value'])->toBe(491.4);
});

test('calculate handles missing inputs as zero', function () {
    $values = MaterialIntakeCalculator::calculate([]);

    expect($values['net_weight_kg'])->toBe(0.0);
    expect($values['total_value'])->toBe(0.0);
});
