<?php

use App\Services\PalletizingProductionCalculator;

test('loss is chips input minus pellets output', function () {
    expect(PalletizingProductionCalculator::lossKg(1087.5, 1018.2))->toBe(69.3);
});

test('loss never drops below zero', function () {
    expect(PalletizingProductionCalculator::lossKg(1018.2, 1087.5))->toBe(0.0);
});

test('loss percentage is loss divided by chips input', function () {
    expect(PalletizingProductionCalculator::lossPercentage(1087.5, 69.3))->toBe(0.0637);
});

test('calculate returns loss and loss percentage', function () {
    $values = PalletizingProductionCalculator::calculate([
        'chips_input_kg' => 1087.5,
        'pellets_output_kg' => 1018.2,
    ]);

    expect($values['loss_kg'])->toBe(69.3);
    expect($values['loss_percentage'])->toBe(0.0637);
});
