<?php

use App\Models\CrushingProduction;
use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('crushing production pdf renders the CR-02 form layout', function () {
    $user = User::factory()->create();
    $material = Material::factory()->create([
        'code' => 'PP',
        'name' => 'Polypropylene',
    ]);
    $intake = MaterialIntake::factory()->create([
        'grn_number' => 'GRN-2026-0001',
        'material_id' => $material->id,
        'recorded_by_user_id' => $user->id,
    ]);
    $production = CrushingProduction::factory()->create([
        'date' => '2026-08-06',
        'batch_number' => 'CR-BATCH-2026-0001',
        'material_intake_id' => $intake->id,
        'grn_reference' => $intake->grn_number,
        'material_id' => $material->id,
        'input_weight_kg' => 1170,
        'output_chips_kg' => 1098.5,
        'loss_kg' => 71.5,
        'loss_percentage' => 0.0611,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/crushing-productions/'.$production->id.'/pdf');

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('crushing-production-CR-BATCH-2026-0001.pdf');

    $html = view('pdf.crushing-production', [
        'production' => $production->load(['material', 'recordedByUser', 'materialIntake']),
    ])->render();

    expect($html)
        ->toContain('Crusher Operator')
        ->toContain('Resident stock controller (verified by)')
        ->toContain('Note: Figures are posted to the Crushing Production Log. Loss % = loss (kg) ÷ input weight (kg).');
});
