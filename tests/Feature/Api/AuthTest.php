<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('login issues a token for valid credentials', function () {
    Permission::firstOrCreate(['name' => 'ViewAny:User', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'username' => 'admin',
        'password' => 'admin123',
        'role' => 'Admin',
    ]);

    $user->assignRole('Admin');

    $response = $this->postJson('/api/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'username', 'role', 'roles', 'permissions']])
        ->assertJsonPath('user.username', 'admin')
        ->assertJsonPath('user.role', 'Admin')
        ->assertJsonPath('user.roles.0', 'Admin')
        ->assertJsonMissingPath('user.password');

    expect($user->tokens()->count())->toBe(1);
});

test('login rejects invalid credentials', function () {
    User::factory()->create([
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $this->postJson('/api/login', [
        'username' => 'admin',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('login rejects unknown usernames', function () {
    $this->postJson('/api/login', [
        'username' => 'nobody',
        'password' => 'admin123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

test('login requires username and password', function () {
    $this->postJson('/api/login', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['username', 'password']);
});

test('login is rate limited', function () {
    User::factory()->create([
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ])->assertOk();
    }

    $this->postJson('/api/login', [
        'username' => 'admin',
        'password' => 'admin123',
    ])->assertTooManyRequests();
});

test('the user endpoint returns the authenticated user', function () {
    Permission::firstOrCreate(['name' => 'ViewAny:Material', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Crusher operator', 'guard_name' => 'web']);

    $user = User::factory()->create(['username' => 'crusher01', 'role' => 'Crusher operator']);
    $user->assignRole('Crusher operator');
    $token = $user->createToken('mobile')->plainTextToken;

    $this->getJson('/api/user', ['Authorization' => 'Bearer '.$token])
        ->assertOk()
        ->assertJsonPath('data.username', 'crusher01')
        ->assertJsonPath('data.roles.0', 'Crusher operator')
        ->assertJsonMissingPath('data.password');
});

test('the user endpoint includes audit logs for the authenticated user details tab', function () {
    $user = User::factory()->create(['username' => 'crusher01']);
    $token = $user->createToken('mobile')->plainTextToken;

    $this->getJson('/api/user', ['Authorization' => 'Bearer '.$token])
        ->assertOk()
        ->assertJsonStructure(['data' => ['audit_logs']]);
});

test('the user endpoint requires authentication', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

test('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $this->postJson('/api/logout', [], ['Authorization' => 'Bearer '.$token])
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.');

    expect($user->tokens()->count())->toBe(0);
});
