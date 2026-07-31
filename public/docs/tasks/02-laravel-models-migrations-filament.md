# Laravel Models, Migrations, And Filament Structure

## Goal

Prepare the backend structure for replacing the current Node.js/SQLite backend with Laravel and a Filament PHP admin panel.

This document should guide the Laravel implementation, not replace the actual migration files.

## Laravel Models

Recommended models:

- `User`
- `Material`
- `MaterialIntake`
- `CrushingProduction`
- `Dispatch`
- `PalletizingReceipt`
- `PalletizingProduction`
- `PelletSale`
- `CashRemittance`

## Migration Files

Suggested migrations:

- `create_users_table`
- `create_materials_table`
- `create_material_intakes_table`
- `create_crushing_productions_table`
- `create_dispatches_table`
- `create_palletizing_receipts_table`
- `create_palletizing_productions_table`
- `create_pellet_sales_table`
- `create_cash_remittances_table`

Use [`../example-data.md`](../example-data.md) as the shared reference for initial
materials, example staff users, workflow seed records, computed values, and field mappings.

## Model Relationships

### User

- `hasMany(MaterialIntake::class, 'recorded_by_user_id')`
- `hasMany(CrushingProduction::class, 'recorded_by_user_id')`
- `hasMany(Dispatch::class, 'recorded_by_user_id')`
- `hasMany(PalletizingReceipt::class, 'recorded_by_user_id')`
- `hasMany(PalletizingProduction::class, 'recorded_by_user_id')`
- `hasMany(PelletSale::class, 'recorded_by_user_id')`
- `hasMany(CashRemittance::class, 'recorded_by_user_id')`

### Material

- `hasMany(MaterialIntake::class)`
- `hasMany(CrushingProduction::class)`
- `hasMany(Dispatch::class)`
- `hasMany(PalletizingReceipt::class)`

### MaterialIntake

- `belongsTo(Material::class)`
- `belongsTo(User::class, 'recorded_by_user_id')`
- `hasMany(CrushingProduction::class)`

### CrushingProduction

- `belongsTo(Material::class)`
- `belongsTo(MaterialIntake::class)`
- `belongsTo(User::class, 'recorded_by_user_id')`
- `hasMany(Dispatch::class)`

### Dispatch

- `belongsTo(Material::class)`
- `belongsTo(CrushingProduction::class)`
- `belongsTo(User::class, 'recorded_by_user_id')`
- `hasMany(PalletizingReceipt::class)`

### PalletizingReceipt

- `belongsTo(Material::class)`
- `belongsTo(Dispatch::class)`
- `belongsTo(User::class, 'recorded_by_user_id')`
- `hasMany(PalletizingProduction::class)`

### PalletizingProduction

- `belongsTo(PalletizingReceipt::class)`
- `belongsTo(User::class, 'recorded_by_user_id')`

### PelletSale

- `belongsTo(User::class, 'recorded_by_user_id')`

### CashRemittance

- `belongsTo(User::class, 'recorded_by_user_id')`

## Filament Resources

Recommended Filament resources:

- `UserResource`
- `MaterialResource`
- `MaterialIntakeResource`
- `CrushingProductionResource`
- `DispatchResource`
- `PalletizingReceiptResource`
- `PalletizingProductionResource`
- `PelletSaleResource`
- `CashRemittanceResource`

## Filament Dashboard Widgets

Suggested widgets:

- Material purchased
- Chips produced
- Crushing loss percentage
- Chips dispatched
- Chips on hand
- Chips received
- Receiving variance
- Payable to crushing
- Pellets produced
- Palletizing loss percentage
- Finished stock
- Sales revenue
- Cash remitted
- Balance retained
- Outstanding to crushing
- Reconciliation status

## Services

Create service classes for business calculations:

- `MaterialIntakeCalculator`
- `CrushingProductionCalculator`
- `PalletizingReceiptCalculator`
- `PalletizingProductionCalculator`
- `PelletSaleCalculator`
- `CashRemittanceCalculator`
- `DashboardSummaryService`

## Policies And Permissions

Use Laravel policies or a permission package for role access.

Initial roles:

- Admin
- Stock controller
- Crusher operator
- Stock receiver
- Palletizing operator
- Supervisor

Admin should manage users and master data. Other roles should only access the workflow screens relevant to their duties.

## Implementation Notes

- Use decimal columns for money and weights.
- Use foreign keys where records can be reliably linked.
- Keep nullable reference text fields during migration to avoid losing old records.
- Use model observers or service classes to calculate derived fields before saving.
- Filament forms should reuse select fields for users, materials, GRNs, batches, and dispatch notes.
