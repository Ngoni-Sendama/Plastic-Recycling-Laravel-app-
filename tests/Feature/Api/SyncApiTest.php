<?php

use App\Models\Material;
use App\Models\MaterialIntake;
use App\Models\PelletSale;
use App\Models\SyncConflict;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function syncAuth(): User
{
    return User::factory()->create(['username' => 'api-user']);
}

function syncHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('mobile')->plainTextToken];
}

test('pull returns records changed since the given timestamp', function () {
    $user = syncAuth();
    $material = Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $old = PelletSale::factory()->create(['receipt_number' => 'RCPT-OLD', 'recorded_by_user_id' => $user->id]);
    $old->forceFill(['updated_at' => now()->subDay()])->save();

    $recent = PelletSale::factory()->create(['receipt_number' => 'RCPT-NEW', 'recorded_by_user_id' => $user->id]);

    $since = now()->subHours(12)->toISOString();

    $this->getJson("/api/sync/pull?since={$since}", syncHeaders($user))
        ->assertOk()
        ->assertJsonStructure([
            'server_time',
            'changes' => [
                'materials',
                'material_intakes',
                'crushing_productions',
                'dispatches',
                'palletizing_receipts',
                'palletizing_productions',
                'pellet_sales',
                'cash_remittances',
            ],
            'deleted',
        ])
        ->assertJsonCount(1, 'changes.materials')
        ->assertJsonCount(1, 'changes.pellet_sales')
        ->assertJsonPath('changes.pellet_sales.0.receipt_number', 'RCPT-NEW')
        ->assertJsonPath('changes.pellet_sales.0.lock_version', 1);
});

test('pull returns soft-deleted records in the deleted section', function () {
    $user = syncAuth();
    $sale = PelletSale::factory()->create(['recorded_by_user_id' => $user->id]);
    $sale->delete();

    $response = $this->getJson('/api/sync/pull', syncHeaders($user));

    $response->assertOk()
        ->assertJsonCount(1, 'deleted.pellet_sales')
        ->assertJsonPath('deleted.pellet_sales.0.id', $sale->id);

    expect($response->json('deleted.pellet_sales.0.deleted_at'))->not->toBeNull();
});

test('push accepts new offline-created records and maps local ids', function () {
    $user = syncAuth();
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);

    $response = $this->postJson('/api/sync/push', [
        'changes' => [
            'materials' => [
                [
                    'local_id' => 'local-material-1',
                    'server_id' => null,
                    'data' => ['code' => 'HDPE', 'name' => 'High-density polyethylene'],
                ],
            ],
            'material_intakes' => [
                [
                    'local_id' => 'local-intake-1',
                    'server_id' => null,
                    'data' => [
                        'date' => '2026-08-01',
                        'grn_number' => 'GRN-SYNC-0001',
                        'buyer_name' => 'Offline Buyer',
                        'material_code' => 'PP',
                        'gross_weight_kg' => 1250,
                        'tare_weight_kg' => 80,
                        'unit_price' => 0.42,
                    ],
                ],
            ],
        ],
    ], syncHeaders($user));

    $response->assertOk()
        ->assertJsonCount(2, 'accepted')
        ->assertJsonCount(0, 'conflicts')
        ->assertJsonCount(0, 'rejected');

    $material = Material::where('code', 'HDPE')->first();
    $intake = MaterialIntake::where('grn_number', 'GRN-SYNC-0001')->first();

    expect($material)->not->toBeNull();
    expect($intake)->not->toBeNull();

    $accepted = $response->json('accepted');

    expect(collect($accepted)->firstWhere('table', 'materials')['server_id'])->toBe($material->id);
    expect(collect($accepted)->firstWhere('table', 'material_intakes')['server_id'])->toBe($intake->id);
    expect(collect($accepted)->firstWhere('table', 'material_intakes')['lock_version'])->toBe(1);

    // Computed values must be applied server-side.
    expect((float) $intake->net_weight_kg)->toBe(1170.0);
    expect((float) $intake->total_value)->toBe(491.4);
});

test('push accepts updates when the lock version matches', function () {
    $user = syncAuth();
    $sale = PelletSale::factory()->create([
        'receipt_number' => 'RCPT-001',
        'customer_name' => 'Old Client',
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/sync/push', [
        'changes' => [
            'pellet_sales' => [
                [
                    'local_id' => 'local-sale-1',
                    'server_id' => $sale->id,
                    'server_lock_version' => 1,
                    'data' => [
                        'kg_sold' => 700,
                        'unit_price' => 0.95,
                    ],
                ],
            ],
        ],
    ], syncHeaders($user));

    $response->assertOk()
        ->assertJsonCount(1, 'accepted')
        ->assertJsonPath('accepted.0.server_id', $sale->id)
        ->assertJsonPath('accepted.0.lock_version', 2);

    $sale->refresh();

    expect((float) $sale->kg_sold)->toBe(700.0);
    expect((float) $sale->amount_received)->toBe(665.0);
    expect($sale->lock_version)->toBe(2);
});

test('push creates a conflict when the lock version is stale', function () {
    $user = syncAuth();
    $sale = PelletSale::factory()->create([
        'receipt_number' => 'RCPT-001',
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $user->id,
    ]);

    // Simulate a competing update that advanced the lock version server-side.
    $sale->update(['kg_sold' => 700, 'amount_received' => 665]);
    $sale->lock_version = 2;
    $sale->save();

    $response = $this->postJson('/api/sync/push', [
        'changes' => [
            'pellet_sales' => [
                [
                    'local_id' => 'local-sale-1',
                    'server_id' => $sale->id,
                    'server_lock_version' => 1,
                    'data' => ['kg_sold' => 500],
                ],
            ],
        ],
    ], syncHeaders($user));

    $response->assertOk()
        ->assertJsonCount(0, 'accepted')
        ->assertJsonCount(1, 'conflicts')
        ->assertJsonCount(0, 'rejected')
        ->assertJsonPath('conflicts.0.server_id', $sale->id)
        ->assertJsonPath('conflicts.0.server_version', 2);

    $conflict = SyncConflict::where('table_name', 'pellet_sales')->first();

    expect($conflict)->not->toBeNull();
    expect($conflict->status)->toBe('pending');
    expect($conflict->submitted_version)->toBe(1);
    expect($conflict->submitted_by_user_id)->toBe($user->id);

    // The server record must not be overwritten.
    expect((float) $sale->refresh()->kg_sold)->toBe(700.0);
});

test('push soft-deletes records with a matching lock version', function () {
    $user = syncAuth();
    $sale = PelletSale::factory()->create([
        'receipt_number' => 'RCPT-001',
        'recorded_by_user_id' => $user->id,
    ]);

    $response = $this->postJson('/api/sync/push', [
        'changes' => [
            'pellet_sales' => [
                [
                    'local_id' => 'local-sale-1',
                    'server_id' => $sale->id,
                    'server_lock_version' => 1,
                    'deleted' => true,
                ],
            ],
        ],
    ], syncHeaders($user));

    $response->assertOk()
        ->assertJsonCount(1, 'accepted')
        ->assertJsonPath('accepted.0.deleted', true)
        ->assertJsonPath('accepted.0.lock_version', 2);

    expect(PelletSale::find($sale->id))->toBeNull();
    expect(PelletSale::withTrashed()->find($sale->id))->not->toBeNull();
});

test('push rejects invalid records without storing them', function () {
    $user = syncAuth();

    $response = $this->postJson('/api/sync/push', [
        'changes' => [
            'pellet_sales' => [
                [
                    'local_id' => 'local-sale-bad',
                    'server_id' => null,
                    'data' => [
                        'receipt_number' => 'RCPT-BAD',
                        'kg_sold' => -5,
                    ],
                ],
            ],
        ],
    ], syncHeaders($user));

    $response->assertOk()
        ->assertJsonCount(0, 'accepted')
        ->assertJsonCount(0, 'conflicts')
        ->assertJsonCount(1, 'rejected')
        ->assertJsonPath('rejected.0.local_id', 'local-sale-bad');

    expect(PelletSale::count())->toBe(0);
});
