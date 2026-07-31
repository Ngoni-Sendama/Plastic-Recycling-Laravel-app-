# Example Data

Use this data when testing the current Node API, building Laravel seeders, or validating
the mobile app after switching endpoints. Field names match the current mobile module
configuration in `mobile/src/modules.js`.

## Login

Seeded admin account:

```json
{
  "username": "admin",
  "password": "admin123"
}
```

Example staff users:

```json
[
  {
    "username": "crusher01",
    "password": "password123",
    "fullName": "Tawanda Moyo",
    "role": "Crusher operator"
  },
  {
    "username": "receiver01",
    "password": "password123",
    "fullName": "Rudo Ndlovu",
    "role": "Stock receiver"
  },
  {
    "username": "supervisor01",
    "password": "password123",
    "fullName": "Nyasha Dube",
    "role": "Supervisor"
  }
]
```

## Materials

Initial material master data for the Laravel version:

```json
[
  { "code": "PP", "name": "Polypropylene" },
  { "code": "HD", "name": "High-density polyethylene" },
  { "code": "LD", "name": "Low-density polyethylene" }
]
```

## Workflow Records

The current Node API accepts each object at `POST /api/records/{module}`. Computed values
are calculated by the backend and returned in the response.

### Material Intake

Endpoint:

```http
POST /api/records/intake
```

Request:

```json
{
  "date": "2026-07-31",
  "grn": "GRN-2026-0001",
  "buyer": "GreenCycle Suppliers",
  "material": "PP",
  "gross": 1250,
  "tare": 80,
  "price": 0.42
}
```

Response includes:

```json
{
  "net": 1170,
  "value": 491.4
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `grn` | `grn_number` |
| `buyer` | `buyer_name` |
| `material` | `material_id` resolved by code |
| `gross` | `gross_weight_kg` |
| `tare` | `tare_weight_kg` |
| `price` | `unit_price` |
| `net` | `net_weight_kg` |
| `value` | `total_value` |

### Crushing Production

Endpoint:

```http
POST /api/records/crushing
```

Request:

```json
{
  "date": "2026-07-31",
  "batch": "CR-BATCH-0001",
  "grnRef": "GRN-2026-0001",
  "material": "PP",
  "input": 1170,
  "output": 1098.5
}
```

Response includes:

```json
{
  "loss": 71.5,
  "lossPct": 0.0611
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `batch` | `batch_number` |
| `grnRef` | `grn_reference` |
| `material` | `material_id` resolved by code |
| `input` | `input_weight_kg` |
| `output` | `output_chips_kg` |
| `loss` | `loss_kg` |
| `lossPct` | `loss_percentage` |

### Dispatch To Palletizing

Endpoint:

```http
POST /api/records/dispatch
```

Request:

```json
{
  "date": "2026-07-31",
  "dispatchNo": "DN-2026-0001",
  "batchRef": "CR-BATCH-0001",
  "material": "PP",
  "weight": 1090,
  "transportedBy": "Highglen Truck 1"
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `dispatchNo` | `dispatch_note_number` |
| `batchRef` | `batch_reference` |
| `material` | `material_id` resolved by code |
| `weight` | `weight_dispatched_kg` |
| `transportedBy` | `transported_by` |

### Palletizing Receipt

Endpoint:

```http
POST /api/records/receipt
```

Request:

```json
{
  "date": "2026-08-01",
  "grn": "PGRN-2026-0001",
  "dispatchRef": "DN-2026-0001",
  "material": "PP",
  "weight": 1087.5,
  "rate": 0.18
}
```

Response includes:

```json
{
  "amount": 195.75
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `grn` | `grn_number` |
| `dispatchRef` | `dispatch_reference` |
| `material` | `material_id` resolved by code |
| `weight` | `weight_received_kg` |
| `rate` | `rate_per_kg` |
| `amount` | `amount_payable` |

### Palletizing Production

Endpoint:

```http
POST /api/records/palletProd
```

Request:

```json
{
  "date": "2026-08-01",
  "batch": "PL-BATCH-0001",
  "grnRef": "PGRN-2026-0001",
  "input": 1087.5,
  "output": 1018.2
}
```

Response includes:

```json
{
  "loss": 69.3,
  "lossPct": 0.0637
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `batch` | `batch_number` |
| `grnRef` | `grn_reference` |
| `input` | `chips_input_kg` |
| `output` | `pellets_output_kg` |
| `loss` | `loss_kg` |
| `lossPct` | `loss_percentage` |

### Pellet Sales

Endpoint:

```http
POST /api/records/sales
```

Request:

```json
{
  "date": "2026-08-02",
  "receiptNo": "SALE-2026-0001",
  "customer": "Metro Plastics",
  "kgSold": 640,
  "price": 0.95
}
```

Response includes:

```json
{
  "amount": 608
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `receiptNo` | `receipt_number` |
| `customer` | `customer_name` |
| `kgSold` | `kg_sold` |
| `price` | `unit_price` |
| `amount` | `amount_received` |

### Cash Remittance

Endpoint:

```http
POST /api/records/remittance
```

Request:

```json
{
  "date": "2026-08-03",
  "voucherNo": "REM-2026-0001",
  "period": "2026-07-31 to 2026-08-02",
  "chipsKg": 1087.5,
  "recoveryPrice": 0.18,
  "salesRevenue": 608,
  "cashRemitted": 500
}
```

Response includes:

```json
{
  "maxDue": 195.75,
  "balanceRetained": 108
}
```

Laravel column mapping:

| Mobile field | Laravel column |
| --- | --- |
| `date` | `date` |
| `voucherNo` | `voucher_number` |
| `period` | `period_covered` |
| `chipsKg` | `chips_delivered_kg` |
| `recoveryPrice` | `recovery_price_per_kg` |
| `salesRevenue` | `sales_revenue` |
| `cashRemitted` | `cash_remitted` |
| `maxDue` | `max_remittance_due` |
| `balanceRetained` | `balance_retained` |

## Dashboard Snapshot

After posting the records above, `GET /api/dashboard` returns arrays grouped by current
module key:

```json
{
  "intake": [
    {
      "date": "2026-07-31",
      "grn": "GRN-2026-0001",
      "buyer": "GreenCycle Suppliers",
      "material": "PP",
      "gross": 1250,
      "tare": 80,
      "price": 0.42,
      "net": 1170,
      "value": 491.4
    }
  ],
  "crushing": [
    {
      "date": "2026-07-31",
      "batch": "CR-BATCH-0001",
      "grnRef": "GRN-2026-0001",
      "material": "PP",
      "input": 1170,
      "output": 1098.5,
      "loss": 71.5,
      "lossPct": 0.0611
    }
  ],
  "dispatch": [
    {
      "date": "2026-07-31",
      "dispatchNo": "DN-2026-0001",
      "batchRef": "CR-BATCH-0001",
      "material": "PP",
      "weight": 1090,
      "transportedBy": "Highglen Truck 1"
    }
  ],
  "receipt": [
    {
      "date": "2026-08-01",
      "grn": "PGRN-2026-0001",
      "dispatchRef": "DN-2026-0001",
      "material": "PP",
      "weight": 1087.5,
      "rate": 0.18,
      "amount": 195.75
    }
  ],
  "palletProd": [
    {
      "date": "2026-08-01",
      "batch": "PL-BATCH-0001",
      "grnRef": "PGRN-2026-0001",
      "input": 1087.5,
      "output": 1018.2,
      "loss": 69.3,
      "lossPct": 0.0637
    }
  ],
  "sales": [
    {
      "date": "2026-08-02",
      "receiptNo": "SALE-2026-0001",
      "customer": "Metro Plastics",
      "kgSold": 640,
      "price": 0.95,
      "amount": 608
    }
  ],
  "remittance": [
    {
      "date": "2026-08-03",
      "voucherNo": "REM-2026-0001",
      "period": "2026-07-31 to 2026-08-02",
      "chipsKg": 1087.5,
      "recoveryPrice": 0.18,
      "salesRevenue": 608,
      "cashRemitted": 500,
      "maxDue": 195.75,
      "balanceRetained": 108
    }
  ]
}
```

## Laravel Seeder Notes

- Create materials first, then users, then workflow records.
- Resolve `material` codes to `materials.id` during seeding.
- Keep text reference fields such as GRN, batch, and dispatch note populated even when a
  matching foreign key exists. This keeps migrated records readable and supports old
  records that cannot be linked cleanly.
- Recalculate `net`, `value`, `loss`, `lossPct`, `amount`, `maxDue`, and
  `balanceRetained` through Laravel service classes instead of hardcoding them in seeders.
