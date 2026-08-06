# Expenses Module Plan

Implementation checklist for the Expenses module and cash-balance flow.

## 1. Database Tables

- [ ] Create `expense_categories` table.
  - Purpose: store controlled expense types like Rent, Transport, Fuel, Wages, Repairs, Miscellaneous.
  - Suggested fields: `id`, `name`, `description`, `is_active`, `created_at`, `updated_at`.
  - Done when: categories can be created, edited, listed, archived, and selected in expense forms.

- [ ] Create `expenses` table.
  - Purpose: store each cash expense against the sales cash pool.
  - Suggested fields: `id`, `expense_number`, `date`, `expense_category_id`, `description`, `amount`, `payment_method`, `recorded_by_user_id`, `lock_version`, `created_at`, `updated_at`, `deleted_at`.
  - Done when: every expense record can be traced to a category, amount, date, and creator.

- [ ] Add sync metadata to the expenses table.
  - Purpose: keep expenses offline-first like the other mobile modules.
  - Suggested fields: `local_id`, `server_id`, `server_lock_version`, `sync_status`, `sync_error`, `last_synced_at`, `deleted_at`.
  - Done when: offline create/update/delete actions can sync later without data loss.

- [ ] Add seeders for common expense categories.
  - Purpose: provide ready-to-use values on first install or remigration.
  - Suggested seed values: Rent, Transport, Fuel, Wages, Repairs, Stationery, Miscellaneous.
  - Done when: a fresh database has categories available without manual entry.

- [ ] Add factories and tests for the new tables.
  - Purpose: support automated testing and repeatable sample data.
  - Done when: feature tests can create expenses and categories reliably.

## 2. Laravel Models and API

- [ ] Create `ExpenseCategory` model.
  - Purpose: represent the category master data.
  - Needed behavior: fillable fields, relationships to expenses, active/inactive support.
  - Done when: categories can be queried and related expenses can be loaded.

- [ ] Create `Expense` model.
  - Purpose: represent a single cash expense transaction.
  - Needed behavior: belongs to category, belongs to user, casts for numeric values and dates.
  - Done when: the model matches the table and returns correct API payloads.

- [ ] Add API routes in `routes/api.php`.
  - Purpose: expose CRUD endpoints for mobile and web.
  - Suggested endpoints:
    - `GET /expense-categories`
    - `POST /expense-categories`
    - `GET /expenses`
    - `GET /expenses/{id}`
    - `POST /expenses`
    - `PATCH /expenses/{id}`
    - `DELETE /expenses/{id}`
  - Done when: the endpoints are available and protected by auth.

- [ ] Add controllers for expense categories and expenses.
  - Purpose: handle listing, show, create, update, delete.
  - Needed behavior: validation, number generation, soft delete, audit trail, and consistent JSON responses.
  - Done when: API requests return predictable payloads and validation errors.

- [ ] Add validation rules and auto-numbering.
  - Purpose: generate expense numbers with a prefix, similar to the other records.
  - Suggested prefix: `EXP-YYYY-0001`.
  - Done when: users do not type the expense number manually.

- [ ] Add API resources.
  - Purpose: normalize output for mobile and Filament.
  - Done when: the mobile app receives stable field names for category, amount, and balance info.

## 3. Filament Dashboard

- [ ] Add a Filament resource for `ExpenseCategory`.
  - Purpose: maintain allowed categories from the admin panel.
  - Done when: admins can create and manage categories in the dashboard.

- [ ] Add a Filament resource for `Expense`.
  - Purpose: manage expense records from the web.
  - Done when: admins can list, create, edit, and delete expenses.

- [ ] Match Filament forms to the mobile fields.
  - Purpose: keep the same business logic across both interfaces.
  - Required fields: number, date, category, description, amount, payment method, recorded by.
  - Done when: web and mobile forms map to the same backend shape.

- [ ] Add navigation grouping.
  - Purpose: keep expense screens grouped with cash-flow modules like sales and remittance.
  - Done when: the sidebar is easy to scan and logically grouped.

- [ ] Add dashboard widgets or stats.
  - Purpose: surface total expenses and remaining cash balance.
  - Suggested widgets: total expenses today, this week, this month, current cash balance.
  - Done when: the dashboard reflects current cash status.

## 4. Balance Formula

- [ ] Define the cash pool formula.
  - Purpose: calculate how much cash is available after sales, remittance, and expenses.
  - Proposed formula:
    - `cash in = total sales revenue`
    - `cash out = remittance + expenses`
    - `closing balance = cash in - cash out`
  - Done when: the formula is documented and used consistently.

- [ ] Decide the balance scope.
  - Options: per day, per sales period, per month, or global running balance.
  - Recommended: support per day and global dashboard totals.
  - Done when: reporting scope is explicitly chosen and implemented.

- [ ] Add a helper/service for balance calculation.
  - Purpose: avoid duplicating the math in multiple screens.
  - Done when: both backend and mobile can use the same logic source.

- [ ] Show balance breakdown in the dashboard.
  - Purpose: let users see how cash moved from sales into remittance and expenses.
  - Done when: the dashboard displays a readable summary of cash flow.

## 5. Mobile App

- [ ] Add an offline-first expenses list screen.
  - Purpose: let users view expenses even without network access.
  - Done when: the list loads from local cache first and refreshes when online.

- [ ] Add an expense create screen.
  - Purpose: record a new expense with auto-generated number and category selection.
  - Required fields: date, category, amount, description, payment method.
  - Done when: the user can create an expense without typing the number.

- [ ] Add an expense details screen.
  - Purpose: show the expense record and available actions.
  - Done when: users can view the full record and print or edit it later if needed.

- [ ] Add an expense edit screen.
  - Purpose: correct an expense after creation.
  - Done when: edits update local data first and sync later.

- [ ] Add delete and sync support.
  - Purpose: keep offline and online records aligned.
  - Done when: delete actions queue offline and sync when connection returns.

- [ ] Add category picker and balance context.
  - Purpose: make it obvious how the expense affects available cash.
  - Done when: the user can pick a category and see the impact on cash balance.

## 6. Reporting and Review

- [ ] Add expenses to the mobile dashboard summary.
  - Purpose: show current cash movement alongside sales and remittance.
  - Done when: dashboard totals include expenses.

- [ ] Show closing cash balance.
  - Purpose: display what remains after subtracting expenses from sales cash.
  - Done when: users can see the remaining balance at a glance.

- [ ] Add filters by date and category.
  - Purpose: support review and auditing.
  - Done when: users can narrow expense records quickly.

- [ ] Verify offline sync.
  - Purpose: ensure create/update/delete works offline and syncs later.
  - Done when: a disconnected device can still record expenses and later push them.

- [ ] Run end-to-end smoke tests.
  - Purpose: confirm the module works on both web and mobile.
  - Done when: the whole cash-flow path passes a basic manual test.

