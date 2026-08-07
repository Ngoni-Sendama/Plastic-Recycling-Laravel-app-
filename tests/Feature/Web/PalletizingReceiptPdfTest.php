<?php

use App\Models\Dispatch;
use App\Models\Material;
use App\Models\PalletizingReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('palletizing receipt pdf renders the PL-01 form layout', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create([
        'code' => 'PP',
        'name' => 'Polypropylene',
    ]);
    $dispatch = Dispatch::factory()->create([
        'dispatch_note_number' => 'DN-2026-0001',
        'material_id' => $material->id,
        'recorded_by_user_id' => $user->id,
    ]);
    $receipt = PalletizingReceipt::factory()->create([
        'date' => '2026-08-07',
        'grn_number' => 'PGRN-2026-0001',
        'dispatch_id' => $dispatch->id,
        'dispatch_reference' => $dispatch->dispatch_note_number,
        'material_id' => $material->id,
        'weight_received_kg' => 1087.5,
        'rate_per_kg' => 0.18,
        'amount_payable' => 195.75,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/palletizing-receipts/'.$receipt->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('palletizing-receipt-PGRN-2026-0001.pdf');

    $html = view('pdf.palletizing-receipt', [
        'receipt' => $receipt->load(['material', 'recordedByUser', 'dispatch']),
    ])->render();

    expect($html)
        ->toContain('Goods Received Note - Crushed Chips')
        ->toContain('Form PL-01 - Palletizing Office')
        ->toContain('Stock receiver (palletizing office)')
        ->toContain('Crushing Office representative')
        ->toContain('Note: Amount payable = weight received (kg) × rate ($/kg, e.g. $0.10/kg). Posted to the Palletizing Receipt Log; liability accrues to the Crushing Office until settled by cash remittance.');
});
