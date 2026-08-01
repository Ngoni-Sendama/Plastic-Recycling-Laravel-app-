<?php

use App\Models\PelletSale;
use App\Models\SyncConflict;
use App\Models\User;
use App\Services\SyncConflictResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function resolverUser(): User
{
    return User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
}

function makeConflict(PelletSale $sale, User $submitter, array $submitted): SyncConflict
{
    return SyncConflict::factory()->create([
        'table_name' => 'pellet_sales',
        'record_id' => $sale->id,
        'submitted_by_user_id' => $submitter->id,
        'server_version' => 1,
        'submitted_version' => 1,
        'server_payload' => $sale->toArray(),
        'submitted_payload' => $submitted,
        'changed_fields' => array_keys($submitted),
        'status' => 'pending',
    ]);
}

test('keepServer keeps the server record and marks the conflict resolved', function () {
    $resolver = resolverUser();
    $submitter = User::factory()->create();

    $sale = PelletSale::factory()->create([
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $submitter->id,
    ]);

    $conflict = makeConflict($sale, $submitter, ['kg_sold' => 700, 'unit_price' => 0.95]);

    app(SyncConflictResolver::class)->keepServer($conflict, $resolver);

    $conflict->refresh();

    expect($conflict->status)->toBe('resolved');
    expect($conflict->resolution)->toBe('keep_server');
    expect($conflict->resolved_by_user_id)->toBe($resolver->id);
    expect($conflict->resolved_at)->not->toBeNull();

    expect((float) $sale->refresh()->kg_sold)->toBe(640.0);
    expect((float) $sale->amount_received)->toBe(608.0);
});

test('acceptSubmitted applies the submitted payload and bumps the lock version', function () {
    $resolver = resolverUser();
    $submitter = User::factory()->create();

    $sale = PelletSale::factory()->create([
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $submitter->id,
    ]);

    $conflict = makeConflict($sale, $submitter, ['kg_sold' => 700, 'unit_price' => 0.95]);

    app(SyncConflictResolver::class)->acceptSubmitted($conflict, $resolver);

    $sale->refresh();

    expect((float) $sale->kg_sold)->toBe(700.0);
    expect((float) $sale->amount_received)->toBe(665.0);
    expect($sale->lock_version)->toBe(2);

    $conflict->refresh();

    expect($conflict->status)->toBe('resolved');
    expect($conflict->resolution)->toBe('accept_submitted');
});

test('mergeFields applies only the selected fields', function () {
    $resolver = resolverUser();
    $submitter = User::factory()->create();

    $sale = PelletSale::factory()->create([
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $submitter->id,
    ]);

    $conflict = makeConflict($sale, $submitter, ['kg_sold' => 700, 'unit_price' => 1.25]);

    app(SyncConflictResolver::class)->mergeFields($conflict, $resolver, ['kg_sold']);

    $sale->refresh();

    expect((float) $sale->kg_sold)->toBe(700.0);
    expect((float) $sale->unit_price)->toBe(0.95);
    expect((float) $sale->amount_received)->toBe(665.0);
    expect($sale->lock_version)->toBe(2);

    $conflict->refresh();

    expect($conflict->resolution)->toBe('manual_merge');
    expect($conflict->status)->toBe('resolved');
});

test('discard marks the conflict discarded without touching the record', function () {
    $resolver = resolverUser();
    $submitter = User::factory()->create();

    $sale = PelletSale::factory()->create([
        'kg_sold' => 640,
        'unit_price' => 0.95,
        'amount_received' => 608,
        'recorded_by_user_id' => $submitter->id,
    ]);

    $conflict = makeConflict($sale, $submitter, ['kg_sold' => 700]);

    app(SyncConflictResolver::class)->discard($conflict, $resolver);

    $conflict->refresh();

    expect($conflict->status)->toBe('discarded');
    expect($conflict->resolution)->toBe('discard_submitted');
    expect($conflict->resolved_by_user_id)->toBe($resolver->id);

    expect((float) $sale->refresh()->kg_sold)->toBe(640.0);
});

test('acceptSubmitted throws when the server record no longer exists', function () {
    $resolver = resolverUser();
    $submitter = User::factory()->create();

    $conflict = SyncConflict::factory()->create([
        'table_name' => 'pellet_sales',
        'record_id' => 99999,
        'submitted_by_user_id' => $submitter->id,
        'submitted_payload' => ['kg_sold' => 700],
        'status' => 'pending',
    ]);

    app(SyncConflictResolver::class)->acceptSubmitted($conflict, $resolver);
})->throws(RuntimeException::class);
