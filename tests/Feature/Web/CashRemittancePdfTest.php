<?php

use App\Models\CashRemittance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cash remittance pdf renders the PL-04 form layout', function () {
    $user = User::factory()->create();
    $remittance = CashRemittance::factory()->create([
        'date' => '2026-08-07',
        'voucher_number' => 'REM-2026-0001',
        'period_covered' => '2026-07-31 to 2026-08-02',
        'chips_delivered_kg' => 1087.5,
        'recovery_price_per_kg' => 0.18,
        'sales_revenue' => 608,
        'cash_remitted' => 500,
        'max_remittance_due' => 195.75,
        'balance_retained' => 108,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/cash-remittances/'.$remittance->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('cash-remittance-REM-2026-0001.pdf');

    $html = view('pdf.cash-remittance', [
        'remittance' => $remittance->load('recordedByUser'),
    ])->render();

    expect($html)
        ->toContain('Cash Remittance Voucher')
        ->toContain('Form PL-04 - Palletizing Office to Crushing Office')
        ->toContain('Remitted by (supervisor, palletizing office)')
        ->toContain('Received by (crushing office representative)')
        ->toContain('Note: Remittance is capped at the value of chips delivered, at the pre-agreed recovery price (e.g. $0.50/kg). Any sales proceeds above that cap are retained by the Palletizing Office as its margin. Posted to the Cash Remittance Log.');
});
