<?php

use App\Models\PrinterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links a printer setting to the owning user', function (): void {
    $user = User::factory()->create();

    $setting = PrinterSetting::query()->create([
        'user_id' => $user->id,
        'printer_name' => 'POS58 Printer',
    ]);

    expect($setting->user_id)->toBe($user->id)
        ->and($setting->user->is($user))->toBeTrue();
});
