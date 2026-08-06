<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Database\Seeders\ExpenseCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function expenseApiUser(): User
{
    return apiUser('Admin', ['username' => 'expense-api-user']);
}

function expenseApiHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('mobile')->plainTextToken];
}

test('expense endpoints require authentication', function () {
    $this->getJson('/api/expenses')->assertUnauthorized();
    $this->postJson('/api/expenses', [])->assertUnauthorized();
    $this->getJson('/api/expense-categories')->assertUnauthorized();
    $this->postJson('/api/expense-categories', [])->assertUnauthorized();
});

test('expense categories can be created and listed', function () {
    $user = expenseApiUser();

    $response = $this->postJson('/api/expense-categories', [
        'name' => 'Transport',
        'description' => 'Fuel and delivery charges.',
    ], expenseApiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Transport')
        ->assertJsonPath('data.is_active', true);

    $this->getJson('/api/expense-categories', expenseApiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Transport');
});

test('expense categories can be updated', function () {
    $user = expenseApiUser();
    $category = ExpenseCategory::factory()->create(['name' => 'Transport']);

    $this->patchJson('/api/expense-categories/'.$category->id, [
        'name' => 'Transport & Logistics',
    ], expenseApiHeaders($user))
        ->assertOk()
        ->assertJsonPath('data.name', 'Transport & Logistics');
});

test('expense categories can be deleted', function () {
    $user = expenseApiUser();
    $category = ExpenseCategory::factory()->create();

    $this->deleteJson('/api/expense-categories/'.$category->id, [], expenseApiHeaders($user))
        ->assertOk()
        ->assertJsonPath('message', 'Expense category deleted successfully.');
});

test('expenses can be created with auto-generated number', function () {
    $user = expenseApiUser();
    $category = ExpenseCategory::factory()->create(['name' => 'Fuel']);

    $response = $this->postJson('/api/expenses', [
        'date' => '2026-08-06',
        'expense_category_id' => $category->id,
        'amount' => 150.00,
        'payment_method' => 'Cash',
        'description' => 'Fuel for delivery truck',
    ], expenseApiHeaders($user));

    $response->assertCreated()
        ->assertJsonPath('data.expense_number', 'EXP-2026-0001')
        ->assertJsonPath('data.amount', 150)
        ->assertJsonPath('data.category.name', 'Fuel');

    $this->getJson('/api/expenses', expenseApiHeaders($user))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.expense_number', 'EXP-2026-0001');
});

test('expenses can be updated', function () {
    $user = expenseApiUser();
    $category = ExpenseCategory::factory()->create(['name' => 'Fuel']);
    $updatedCategory = ExpenseCategory::factory()->create(['name' => 'Repairs']);

    $created = $this->postJson('/api/expenses', [
        'date' => '2026-08-06',
        'expense_category_id' => $category->id,
        'amount' => 150.00,
        'payment_method' => 'Cash',
    ], expenseApiHeaders($user))->assertCreated()->json('data');

    $this->patchJson('/api/expenses/'.$created['id'], [
        'date' => '2026-08-07',
        'expense_category_id' => $updatedCategory->id,
        'amount' => 200.00,
        'payment_method' => 'Bank Transfer',
    ], expenseApiHeaders($user))
        ->assertOk()
        ->assertJsonPath('data.amount', 200)
        ->assertJsonPath('data.category.name', 'Repairs');
});

test('expenses can be deleted', function () {
    $user = expenseApiUser();
    $category = ExpenseCategory::factory()->create();
    $expense = Expense::factory()->create(['expense_category_id' => $category->id]);

    $this->deleteJson('/api/expenses/'.$expense->id, [], expenseApiHeaders($user))
        ->assertOk()
        ->assertJsonPath('message', 'Expense deleted successfully.');
});

test('expense validation errors are returned in the documented shape', function () {
    $user = expenseApiUser();

    $response = $this->postJson('/api/expenses', [], expenseApiHeaders($user));

    $response->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors']);
});

test('expense categories are seeded', function () {
    (new ExpenseCategorySeeder)->run();

    $this->assertDatabaseCount('expense_categories', 7);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Rent']);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Transport']);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Fuel']);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Wages']);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Repairs']);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Stationery']);
    $this->assertDatabaseHas('expense_categories', ['name' => 'Miscellaneous']);
});
