<?php

use App\Models\AuditLog;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api writes audit logs when materials are created', function () {
    $auth = apiUser('Admin', ['username' => 'admin', 'role' => 'Admin']);

    $response = $this->actingAs($auth, 'sanctum')
        ->postJson('/api/materials', [
            'code' => 'PP',
            'name' => 'Polypropylene',
        ]);

    $response->assertCreated();

    expect(AuditLog::where('auditable_type', Material::class)->count())->toBe(1);

    $log = AuditLog::where('auditable_type', Material::class)->first();

    expect($log?->user_id)->toBe($auth->id)
        ->and($log?->action)->toBe('created')
        ->and($log?->auditable_type)->toBe(Material::class)
        ->and($log?->source)->toBe('mobile_api')
        ->and($log?->new_values)->toMatchArray([
            'code' => 'PP',
            'name' => 'Polypropylene',
        ]);
});

test('audit logs endpoint lists recent entries', function () {
    $auth = apiUser('Admin', ['username' => 'admin', 'role' => 'Admin']);

    $this->actingAs($auth, 'sanctum')
        ->postJson('/api/materials', [
            'code' => 'HDPE',
            'name' => 'High-density polyethylene',
        ])
        ->assertCreated();

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/audit-logs')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'action', 'description', 'source', 'created_at']]]);
});
