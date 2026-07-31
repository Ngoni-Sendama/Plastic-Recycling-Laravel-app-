# Database Tables And Relationships

## Goal

Convert the current flexible JSON record storage into proper relational database tables.

The current backend stores workflow entries in one `records` table with a `module` column and JSON `data`. The Laravel backend should instead use dedicated tables with clear columns, relationships, indexes, and constraints.

## Current Workflows

The app currently records these workflow modules:

- Material intake
- Crushing production
- Dispatch to palletizing
- Palletizing receipt
- Palletizing production
- Pellet sales
- Cash remittance

Concrete sample rows and mobile-to-Laravel field mappings are in
[`../example-data.md`](../example-data.md).

## Suggested Core Tables

### users

Stores staff accounts.

Main columns:

- id
- name
- username
- email nullable
- password
- role
- timestamps

Relationships:

- Has many material intakes
- Has many crushing productions
- Has many dispatches
- Has many palletizing receipts
- Has many palletizing productions
- Has many pellet sales
- Has many cash remittances

### materials

Stores material types instead of hardcoding `PP`, `HD`, and `LD`.

Main columns:

- id
- code
- name
- timestamps

Relationships:

- Has many material intakes
- Has many crushing productions
- Has many dispatches
- Has many palletizing receipts

### material_intakes

Stores incoming raw material records.

Main columns:

- id
- date
- grn_number
- buyer_name
- material_id
- gross_weight_kg
- tare_weight_kg
- net_weight_kg
- unit_price
- total_value
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to material
- Belongs to recorded-by user
- Can be referenced by crushing production records

### crushing_productions

Stores crushing batch records.

Main columns:

- id
- date
- batch_number
- material_intake_id nullable
- grn_reference nullable
- material_id
- input_weight_kg
- output_chips_kg
- loss_kg
- loss_percentage
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to material
- Belongs to material intake, when matched
- Belongs to recorded-by user
- Has many dispatches

### dispatches

Stores dispatches from crushing to palletizing.

Main columns:

- id
- date
- dispatch_note_number
- crushing_production_id nullable
- batch_reference nullable
- material_id
- weight_dispatched_kg
- transported_by nullable
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to crushing production, when matched
- Belongs to material
- Belongs to recorded-by user
- Can be referenced by palletizing receipts

### palletizing_receipts

Stores receipt of chips at palletizing.

Main columns:

- id
- date
- grn_number
- dispatch_id nullable
- dispatch_reference nullable
- material_id
- weight_received_kg
- rate_per_kg
- amount_payable
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to dispatch, when matched
- Belongs to material
- Belongs to recorded-by user
- Can be referenced by palletizing production records

### palletizing_productions

Stores pelletizing production records.

Main columns:

- id
- date
- batch_number
- palletizing_receipt_id nullable
- grn_reference nullable
- chips_input_kg
- pellets_output_kg
- loss_kg
- loss_percentage
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to palletizing receipt, when matched
- Belongs to recorded-by user
- Has many pellet sales through produced stock logic, if batch-level stock tracking is later added

### pellet_sales

Stores pellet sales records.

Main columns:

- id
- date
- receipt_number
- customer_name
- kg_sold
- unit_price
- amount_received
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to recorded-by user

### cash_remittances

Stores remittance records.

Main columns:

- id
- date
- voucher_number
- period_covered nullable
- chips_delivered_kg
- recovery_price_per_kg
- sales_revenue
- cash_remitted
- max_remittance_due
- balance_retained
- recorded_by_user_id
- timestamps

Relationships:

- Belongs to recorded-by user

## Important Decisions

- Decide whether references such as GRN, batch number, and dispatch note should remain text references or become required foreign keys.
- Decide whether to allow unmatched historical records during migration from JSON.
- Decide whether stock should be calculated only from totals or tracked per batch.
- Decide whether material prices and recovery prices need history tables.

## Implementation Notes

- Keep computed values stored in columns for reporting speed and audit stability.
- Also keep formulas in backend services so values are consistently recalculated when creating or editing records.
- Add indexes for common lookup fields such as `date`, `grn_number`, `batch_number`, `dispatch_note_number`, and foreign keys.
