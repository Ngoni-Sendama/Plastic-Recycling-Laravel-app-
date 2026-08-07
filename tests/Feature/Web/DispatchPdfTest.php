<?php

use App\Models\CrushingProduction;
use App\Models\Dispatch;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dispatch pdf renders the CR-03 form layout', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create([
        'code' => 'PP',
        'name' => 'Polypropylene',
    ]);
    $production = CrushingProduction::factory()->create([
        'batch_number' => 'CR-BATCH-2026-0001',
        'material_id' => $material->id,
        'recorded_by_user_id' => $user->id,
    ]);
    $dispatch = Dispatch::factory()->create([
        'date' => '2026-08-07',
        'dispatch_note_number' => 'DN-2026-0001',
        'crushing_production_id' => $production->id,
        'batch_reference' => $production->batch_number,
        'material_id' => $material->id,
        'weight_dispatched_kg' => 1098.5,
        'transported_by' => 'Highglen Truck 1',
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/dispatches/'.$dispatch->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('dispatch-DN-2026-0001.pdf');

    $html = view('pdf.dispatch-note', [
        'dispatch' => $dispatch->load(['material', 'recordedByUser', 'crushingProduction']),
    ])->render();

    expect($html)
        ->toContain('Stock Dispatch Note')
        ->toContain('Form CR-03 - Crushing Office to Palletizing Office')
        ->toContain('Dispatched by (stock controller)')
        ->toContain('Received by (palletizing office, on arrival)')
        ->toContain('Note: Three copies – one travels with the load, one stays with the Crushing Office stock controller, one is filed for the Palletizing Office to countersign on receipt.');
});
