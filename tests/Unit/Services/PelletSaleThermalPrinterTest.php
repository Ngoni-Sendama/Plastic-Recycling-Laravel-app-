<?php

use App\Services\PelletSaleThermalPrinter;
use RuntimeException;
use Tests\TestCase;

uses(TestCase::class);

test('windows connector requires a configured printer name', function (): void {
    config()->set('escpos.connection', 'windows');
    config()->set('escpos.name', '');

    $service = new PelletSaleThermalPrinter;
    $method = new ReflectionMethod($service, 'connector');
    $method->setAccessible(true);

    expect(fn (): mixed => $method->invoke($service))
        ->toThrow(RuntimeException::class, 'ESC/POS printer name is not configured.');
});
