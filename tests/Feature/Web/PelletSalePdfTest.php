<?php

use App\Models\PelletSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pellet sale pdf renders the PL-03 form layout', function () {
    $user = User::factory()->create();
    $sale = PelletSale::factory()->create([
        'date' => '2026-08-07',
        'receipt_number' => 'SALE-2026-0001',
        'customer_name' => 'Metro Plastics',
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/pellet-sales/'.$sale->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('pellet-sale-SALE-2026-0001.pdf');

    $html = view('pdf.sale-receipt', [
        'sale' => $sale->load('recordedByUser'),
    ])->render();

    expect($html)
        ->toContain('Pellet Sales - Cash Sale Receipt')
        ->toContain('Form PL-03 - Palletizing Office')
        ->toContain('Sold by (supervisor)')
        ->toContain('Customer')
        ->toContain('Note: Posted to the Pellet Sales Log same day. Cash received funds the cash remittance to the Crushing Office and the balance retained by the Palletizing Office.');
});
