<?php

use App\Services\CrushingProductionCalculator;

test('loss is input minus output chips', function () {
    expect(CrushingProductionCalculator::lossKg(1170, 1098.5))->toBe(71.5);
});

test('loss never drops below zero', function () {
    expect(CrushingProductionCalculator::lossKg(1098.5, 1170))->toBe(0.0);
});

test('loss percentage is loss divided by input', function () {
    expect(CrushingProductionCalculator::lossPercentage(1170, 71.5))->toBe(0.0611);
});

test('loss percentage is zero when input is zero', function () {
    expect(CrushingProductionCalculator::lossPercentage(0, 71.5))->toBe(0.0);
});

test('calculate returns loss and loss percentage', function () {
    $values = CrushingProductionCalculator::calculate([
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
    ]);

    expect($values['loss_kg'])->toBe(71.5);
    expect($values['loss_percentage'])->toBe(0.0611);
});
