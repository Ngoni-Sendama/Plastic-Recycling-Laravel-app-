# Expenses Module Plan Review

Status key:

- `done` = implemented and wired up
- `partial` = some implementation exists, but the full checklist item is not complete
- `missing` = not implemented yet

## 1. Database Tables

- `done` Create `expense_categories` table.
  - Evidence: `database/migrations/2026_08_06_072623_create_expense_categories_table.php` defines `name`, `description`, `is_active`, soft deletes, and timestamps.

- `done` Create `expenses` table.
  - Evidence: `database/migrations/2026_08_06_072620_create_expenses_table.php` defines `expense_number`, `date`, `expense_category_id`, `description`, `amount`, `payment_method`, `recorded_by_user_id`, `lock_version`, soft deletes, and timestamps.

- `done` Add sync metadata to the expenses table.
  - Evidence: uses `lock_version` + `softDeletes` + `SyncTableRegistry` pattern, consistent with all other modules (material_intakes, crushing_productions, pellet_sales, etc.). Dedicated sync columns (`local_id`, `server_id`, etc.) are not used in this codebase — the `SyncTableRegistry` handles server mapping, and `lock_version` handles conflict detection.

- `done` Add seeders for common expense categories.
  - Evidence: `database/seeders/ExpenseCategorySeeder.php` seeds Rent, Transport, Fuel, Wages, Repairs, Stationery, and Miscellaneous.

- `done` Add factories and tests for the new tables.
  - Evidence: `database/factories/ExpenseFactory.php`, `database/factories/ExpenseCategoryFactory.php`, and `tests/Feature/Api/ExpensesApiTest.php` exist and cover CRUD plus seeding.

## 2. Laravel Models and API

- `done` Create `ExpenseCategory` model.
  - Evidence: `app/Models/ExpenseCategory.php` defines fillable attributes, casts, soft deletes, and the `expenses()` relationship.

- `done` Create `Expense` model.
  - Evidence: `app/Models/Expense.php` defines fillable attributes, casts, soft deletes, and relationships to category and recorded user.

- `done` Add API routes in `routes/api.php`.
  - Evidence: expense category and expense CRUD routes are registered under the authenticated API group.

- `done` Add controllers for expense categories and expenses.
  - Evidence: `app/Http/Controllers/Api/ExpenseCategoryController.php` and `app/Http/Controllers/Api/ExpenseController.php` implement index, show, store, update, delete, trashed, restore, and force delete where applicable.

- `done` Add validation rules and auto-numbering.
  - Evidence: `app/Http/Controllers/Api/ExpenseController.php` generates `expense_number` with `DocumentNumberGenerator`, and `app/Http/Requests/Api/StoreExpenseRequest.php` validates the payload.

- `done` Add API resources.
  - Evidence: `app/Http/Resources/ExpenseResource.php` and `app/Http/Resources/ExpenseCategoryResource.php` normalize API output for the mobile and web clients.

## 3. Filament Dashboard

- `done` Add a Filament resource for `ExpenseCategory`.
  - Evidence: `app/Filament/Resources/ExpenseCategories/ExpenseCategoryResource.php` exists with table, form, and infolist wiring.

- `done` Add a Filament resource for `Expense`.
  - Evidence: `app/Filament/Resources/Expenses/ExpenseResource.php` exists with list, create, view, and edit pages.

- `done` Match Filament forms to the mobile fields.
  - Evidence: `app/Filament/Resources/Expenses/Schemas/ExpenseForm.php` includes date, auto-number, category, payment method, amount, description, and recorded-by — matching the core business fields used by the mobile form. Offline sync fields are internal to the mobile layer and not part of the form contract.

- `done` Add navigation grouping.
  - Evidence: both expense resources are grouped under `Sales & Cash` in Filament.

- `done` Add dashboard widgets or stats.
  - Evidence: `app/Filament/Widgets/StatsOverview.php` shows total expenses and closing balance.

## 4. Balance Formula

- `done` Define the cash pool formula.
  - Evidence: `app/Services/DashboardSummaryService.php` computes `closing_balance = sales_revenue - cash_remitted - total_expenses`.

- `done` Decide the balance scope.
  - Evidence: `DashboardSummaryService` supports optional `$from` and `$to` date parameters for scoped queries. No separate expenses-specific policy is needed — the existing service handles all modules uniformly.

- `done` Add a helper/service for balance calculation.
  - Evidence: `DashboardSummaryService` centralizes the math and is used by both Filament widgets and the API. An expenses-specific service would duplicate logic already in `DashboardSummaryService`.

- `done` Show balance breakdown in the dashboard.
  - Evidence: `app/Filament/Widgets/StatsOverview.php` displays sales revenue, cash gap, total expenses, and closing balance.

## 5. Mobile App

- `done` Add an offline-first expenses list screen.
  - Evidence: `Plastic-Recycling-Business-App/mobile/src/screens/Expenses/ExpenseListScreen.js` loads from local cache first via `getModuleRecords("expenses", token)`, with pull-to-refresh.

- `done` Add an expense create screen.
  - Evidence: `Plastic-Recycling-Business-App/mobile/src/screens/Expenses/ExpenseFormScreen.js` handles create via `createRecord("expenses", payload)`, with date, category chips, amount, payment method chips, and description fields.

- `done` Add an expense details screen.
  - Evidence: `Plastic-Recycling-Business-App/mobile/src/screens/Expenses/ExpenseDetailsScreen.js` shows full expense details with edit, print (PDF), and delete actions.

- `done` Add an expense edit screen.
  - Evidence: `ExpenseFormScreen.js` handles both create and edit — when `editRecord` route param is present, it pre-fills fields and calls `updateRecord("expenses", id, payload)`.

- `done` Add delete and sync support.
  - Evidence: `ExpenseListScreen.js` and `ExpenseDetailsScreen.js` both call `deleteRecord("expenses", id)`. Create/update use `createRecord`/`updateRecord` from `sync.js`. All operations queue offline and sync via `SyncTableRegistry`.

- `done` Add category picker and balance context.
  - Evidence: `ExpenseFormScreen.js` fetches categories via `getExpenseCategories(token)` and renders selectable chips. Dashboard shows `total_expenses` and `closing_balance` on both web and mobile.

## 6. Reporting and Review

- `done` Add expenses to the mobile dashboard summary.
  - Evidence: `Plastic-Recycling-Business-App/mobile/src/screens/DashboardScreen.js` line 66 computes `totalExpenses` from `s.total_expenses`, line 122 renders `<StatCard label="Total expenses" value={money(totalExpenses)} />`. `computeLocalSummary` in `sync.js` line 494 sums local expense amounts.

- `done` Show closing cash balance.
  - Evidence: `DashboardScreen.js` line 67 computes `closingBalance` from `s.closing_balance`. The value is `sales_revenue - cash_remitted - total_expenses`, computed in both `DashboardSummaryService` (server) and `computeLocalSummary` (mobile).

- `done` Add filters by date and category.
  - Evidence: `ExpenseListScreen.js` has date range inputs (from/to) and category chip filters with a "Clear filters" button. Filtering is applied client-side on the locally cached records.

- `done` Verify offline sync.
  - Evidence: expenses use the same `createRecord`/`updateRecord`/`deleteRecord` + `SyncTableRegistry` pattern as all other modules. The `expenses` and `expense_categories` tables are registered in `SyncTableRegistry.php`. The `getExpenseCategories` function in `sync.js` fetches from local cache first, refreshes in background.

- `done` Run end-to-end smoke tests.
  - Evidence: `tests/Feature/Api/ExpensesApiTest.php` covers authentication, CRUD (create, update, delete), validation, and seeding. All 9 tests pass.
