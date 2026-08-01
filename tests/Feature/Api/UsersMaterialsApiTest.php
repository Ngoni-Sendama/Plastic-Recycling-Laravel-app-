<?php

use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the users index endpoint lists users', function () {
    $auth = User::factory()->create(['name' => 'Admin User', 'username' => 'admin', 'role' => 'Admin']);
    User::factory()->create(['name' => 'Crusher User', 'username' => 'crusher01', 'role' => 'Crusher operator']);

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.username', 'admin')
        ->assertJsonPath('data.1.username', 'crusher01');
});

test('the users index endpoint filters by username search', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    User::factory()->create(['name' => 'Crusher One', 'username' => 'crusher01', 'role' => 'Crusher operator']);
    User::factory()->create(['name' => 'Crusher Two', 'username' => 'crusher02', 'role' => 'Crusher operator']);
    User::factory()->create(['name' => 'Receiver One', 'username' => 'receiver01', 'role' => 'Stock receiver']);

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/users?search=crusher')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.username', 'crusher01')
        ->assertJsonPath('data.1.username', 'crusher02');

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/users?search=nobody')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('users can be created via the API', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);

    $response = $this->actingAs($auth, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Tawanda Moyo',
            'username' => 'tawanda',
            'email' => 'tawanda@example.com',
            'password' => 'secret123',
            'role' => 'Supervisor',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.username', 'tawanda')
        ->assertJsonPath('data.role', 'Supervisor')
        ->assertJsonMissingPath('data.password');

    expect(User::where('username', 'tawanda')->exists())->toBeTrue();
});

test('the users show endpoint returns a single user', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    $user = User::factory()->create(['username' => 'crusher01']);

    $this->actingAs($auth, 'sanctum')
        ->getJson("/api/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('data.username', 'crusher01');
});

test('users can be updated via the API', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    $user = User::factory()->create(['username' => 'crusher01', 'role' => 'Crusher operator']);

    $this->actingAs($auth, 'sanctum')
        ->patchJson("/api/users/{$user->id}", [
            'role' => 'Supervisor',
        ])
        ->assertOk()
        ->assertJsonPath('data.role', 'Supervisor');

    expect($user->refresh()->role)->toBe('Supervisor');
});

test('users can be deleted via the API', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    $user = User::factory()->create(['username' => 'crusher01']);

    $this->actingAs($auth, 'sanctum')
        ->deleteJson("/api/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('message', 'User deleted successfully.');

    expect(User::find($user->id))->toBeNull();
});

test('user validation rejects duplicate usernames', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    User::factory()->create(['username' => 'crusher01']);

    $this->actingAs($auth, 'sanctum')
        ->postJson('/api/users', [
            'name' => 'Tawanda Moyo',
            'username' => 'crusher01',
            'password' => 'secret123',
            'role' => 'Supervisor',
        ])
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors']);
});

test('the materials index endpoint lists materials', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    Material::factory()->create(['code' => 'PP', 'name' => 'Polypropylene']);
    Material::factory()->create(['code' => 'HDPE', 'name' => 'High-density polyethylene']);

    $this->actingAs($auth, 'sanctum')
        ->getJson('/api/materials')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.code', 'HDPE');
});

test('materials can be created via the API', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);

    $this->actingAs($auth, 'sanctum')
        ->postJson('/api/materials', [
            'code' => 'PP',
            'name' => 'Polypropylene',
        ])
        ->assertCreated()
        ->assertJsonPath('data.code', 'PP')
        ->assertJsonPath('data.lock_version', 1);
});

test('materials can be updated via the API', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    $material = Material::factory()->create(['code' => 'PP']);

    $this->actingAs($auth, 'sanctum')
        ->patchJson("/api/materials/{$material->id}", [
            'name' => 'Recycled Polypropylene',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Recycled Polypropylene');
});

test('material validation rejects duplicate codes', function () {
    $auth = User::factory()->create(['username' => 'admin', 'role' => 'Admin']);
    Material::factory()->create(['code' => 'PP']);

    $this->actingAs($auth, 'sanctum')
        ->postJson('/api/materials', [
            'code' => 'PP',
            'name' => 'Duplicate',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

test('users, materials and dashboard endpoints require authentication', function () {
    $this->getJson('/api/users')->assertUnauthorized();
    $this->getJson('/api/materials')->assertUnauthorized();
    $this->getJson('/api/dashboard')->assertUnauthorized();
    $this->getJson('/api/sync/pull')->assertUnauthorized();
    $this->postJson('/api/sync/push', [])->assertUnauthorized();
});
