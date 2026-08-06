# Expenses Module Plan

Status key:

- `done` = implemented in code and wired up
- `partial` = some pieces exist, but the checklist item is not fully complete
- `missing` = not implemented yet

## 1. Database Tables

- `done` Create `expense_categories` table.
  - Purpose: store controlled expense types like Rent, Transport, Fuel, Wages, Repairs, Miscellaneous.
  - Suggested fields: `id`, `name`, `description`, `is_active`, `created_at`, `updated_at`.
  - Done when: categories can be created, edited, listed, archived, and selected in expense forms.

- `done` Create `expenses` table.
  - Purpose: store each cash expense against the sales cash pool.
  - Suggested fields: `id`, `expense_number`, `date`, `expense_category_id`, `description`, `amount`, `payment_method`, `recorded_by_user_id`, `lock_version`, `created_at`, `updated_at`, `deleted_at`.
  - Done when: every expense record can be traced to a category, amount, date, and creator.

- `done` Add sync metadata to the expenses table.
  - Purpose: keep expenses offline-first like the other mobile modules.
  - Implementation: uses `lock_version` + `softDeletes` + `SyncTableRegistry` pattern, consistent with all other modules.
  - Done when: offline create/update/delete actions can sync later without data loss.

- `done` Add seeders for common expense categories.
  - Purpose: provide ready-to-use values on first install or remigration.
  - Seed values: Rent, Transport, Fuel, Wages, Repairs, Stationery, Miscellaneous.
  - Done when: a fresh database has categories available without manual entry.

- `done` Add factories and tests for the new tables.
  - Purpose: support automated testing and repeatable sample data.
  - `ExpenseFactory` and `ExpenseCategoryFactory` in `database/factories/`.
  - `ExpensesApiTest` with 9 tests in `tests/Feature/Api/ExpensesApiTest.php`.
  - Done when: feature tests can create expenses and categories reliably.

## 2. Laravel Models and API

- `done` Create `ExpenseCategory` model.
  - Purpose: represent the category master data.
  - Needed behavior: fillable fields, relationships to expenses, active/inactive support.
  - Done when: categories can be queried and related expenses can be loaded.

- `done` Create `Expense` model.
  - Purpose: represent a single cash expense transaction.
  - Needed behavior: belongs to category, belongs to user, casts for numeric values and dates.
  - Done when: the model matches the table and returns correct API payloads.

- `done` Add API routes in `routes/api.php`.
  - Purpose: expose CRUD endpoints for mobile and web.
  - Endpoints:
    - `GET /expense-categories`
    - `POST /expense-categories`
    - `GET /expenses`
    - `GET /expenses/{id}`
    - `POST /expenses`
    - `PATCH /expenses/{id}`
    - `DELETE /expenses/{id}`
    - `POST /expenses/{id}/restore`
    - `DELETE /expenses/{id}/force`
  - Done when: the endpoints are available and protected by auth.

- `done` Add controllers for expense categories and expenses.
  - Purpose: handle listing, show, create, update, delete.
  - Needed behavior: validation, number generation, soft delete, audit trail, and consistent JSON responses.
  - Done when: API requests return predictable payloads and validation errors.

- `done` Add validation rules and auto-numbering.
  - Purpose: generate expense numbers with a prefix, similar to the other records.
  - Prefix: `EXP-YYYY-0001`.
  - Done when: users do not type the expense number manually.

- `done` Add API resources.
  - Purpose: normalize output for mobile and Filament.
  - `ExpenseResource` and `ExpenseCategoryResource` in `app/Http/Resources/`.
  - Done when: the mobile app receives stable field names for category, amount, and balance info.

## 3. Filament Dashboard

- `done` Add a Filament resource for `ExpenseCategory`.
  - Purpose: maintain allowed categories from the admin panel.
  - Done when: admins can create and manage categories in the dashboard.

- `done` Add a Filament resource for `Expense`.
  - Purpose: manage expense records from the web.
  - Done when: admins can list, create, edit, and delete expenses.

- `done` Match Filament forms to the mobile fields.
  - Purpose: keep the same business logic across both interfaces.
  - Fields: date, expense_number (auto-generated), category, payment_method, amount, description, recorded_by.
  - Done when: web and mobile forms map to the same backend shape.

- `done` Add navigation grouping.
  - Purpose: keep expense screens grouped with cash-flow modules like sales and remittance.
  - Done when: the sidebar is easy to scan and logically grouped.

- `done` Add dashboard widgets or stats.
  - Purpose: surface total expenses and remaining cash balance.
  - `StatsOverview` widget shows `total_expenses` and `closing_balance`.
  - Done when: the dashboard reflects current cash status.

## 4. Balance Formula

- `done` Define the cash pool formula.
  - Purpose: calculate how much cash is available after sales, remittance, and expenses.
  - Formula:
    - `cash in = total sales revenue`
    - `cash out = remittance + expenses`
    - `closing balance = cash in - cash out`
  - Done when: the formula is documented and used consistently.

- `done` Decide the balance scope.
  - Options: per day, per sales period, per month, or global running balance.
  - Implementation: `DashboardSummaryService` accepts optional `$from` and `$to` date parameters.
  - Done when: reporting scope is explicitly chosen and implemented.

- `done` Add a helper/service for balance calculation.
  - Purpose: avoid duplicating the math in multiple screens.
  - `DashboardSummaryService` computes `total_expenses` and `closing_balance`.
  - Done when: both backend and mobile can use the same logic source.

- `done` Show balance breakdown in the dashboard.
  - Purpose: let users see how cash moved from sales into remittance and expenses.
  - Filament `StatsOverview` widget: shows `total_expenses` and `closing_balance`.
  - Mobile `DashboardScreen`: shows "Total expenses" stat card.
  - Done when: the dashboard displays a readable summary of cash flow.

## 5. Mobile App

- `done` Add an offline-first expenses list screen.
  - Purpose: let users view expenses even without network access.
  - `ExpenseListScreen.js` loads from local cache first via `getModuleRecords`.
  - Done when: the list loads from local cache first and refreshes when online.

- `done` Add an expense create screen.
  - Purpose: record a new expense with auto-generated number and category selection.
  - `ExpenseFormScreen.js` with date, category chips, amount, payment method chips, description.
  - Done when: the user can create an expense without typing the number.

- `done` Add an expense details screen.
  - Purpose: show the expense record and available actions.
  - `ExpenseDetailsScreen.js` with edit, print (PDF), and delete actions.
  - Done when: users can view the full record and print or edit it later if needed.

- `done` Add an expense edit screen.
  - Purpose: correct an expense after creation.
  - `ExpenseFormScreen.js` handles both create and edit via `editRecord` route param.
  - Done when: edits update local data first and sync later.

- `done` Add delete and sync support.
  - Purpose: keep offline and online records aligned.
  - `deleteRecord("expenses", ...)` in list and details screens; `createRecord`/`updateRecord` in form.
  - Done when: delete actions queue offline and sync when connection returns.

- `done` Add category picker and balance context.
  - Purpose: make it obvious how the expense affects available cash.
  - Category chips in form; dashboard shows `total_expenses` and `closing_balance`.
  - Done when: the user can pick a category and see the impact on cash balance.

## 6. Reporting and Review

- `done` Add expenses to the mobile dashboard summary.
  - Purpose: show current cash movement alongside sales and remittance.
  - `DashboardScreen.js` shows "Total expenses" stat card; `computeLocalSummary` in sync.js.
  - Done when: dashboard totals include expenses.

- `done` Show closing cash balance.
  - Purpose: display what remains after subtracting expenses from sales cash.
  - `closing_balance` computed in both `DashboardSummaryService` (server) and `computeLocalSummary` (mobile).
  - Done when: users can see the remaining balance at a glance.

- `done` Add filters by date and category.
  - Purpose: support review and auditing.
  - `ExpenseListScreen.js` has date range inputs (from/to) and category chip filters.
  - Done when: users can narrow expense records quickly.

- `done` Verify offline sync.
  - Purpose: ensure create/update/delete works offline and syncs later.
  - Uses same `createRecord`/`updateRecord`/`deleteRecord` + `SyncTableRegistry` as all other modules.
  - Done when: a disconnected device can still record expenses and later push them.

- `done` Run end-to-end smoke tests.
  - Purpose: confirm the module works on both web and mobile.
  - `ExpensesApiTest` covers auth, CRUD, validation, and seeding.
  - Done when: the whole cash-flow path passes a basic manual test.
