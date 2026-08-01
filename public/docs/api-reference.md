# Plastic Recycling — Mobile API Reference

Human-readable companion to `openapi.yaml` (import that file into Swagger UI,
Postman, Insomnia, or Redocly for an interactive spec). This document explains
how the API works and how to integrate it into the React Native app.

---

## 1. Conventions

| Item | Convention |
| --- | --- |
| Base URL | `https://plastic-recycling.test/api` (Herd dev server — swap for production) |
| Auth | Sanctum bearer token — `Authorization: Bearer <token>` on every request except `POST /login` |
| Request body | `application/json` |
| Response envelope | Collections return `{ "data": [...] }`; single records return the object directly; `message`-style responses return `{ "message": ... }` |
| Dates | `date` fields are `YYYY-MM-DD`; timestamps are ISO-8601 (`2026-08-01T10:00:00.000000Z`) |
| Numbers | Amounts/weights are decimals (JSON numbers, e.g. `1050.5`) |
| Errors | `422 { "message": "...", "errors": { "field": ["..."] } }` for validation; `401` when unauthenticated; `429` when rate-limited |
| Derived fields | **Never send calculated values** — `net_weight_kg`, `total_value`, `loss_kg`, `loss_percentage`, `amount_payable`, `amount_received`, `max_remittance_due`, `balance_retained` are computed server-side and returned in the response |
| Material reference | On workflow endpoints, send either `material_id` or `material_code`. If both are sent, **`material_code` wins** |

---

## 2. Authentication

### 2.1 Login — `POST /login`

```json
{
  "username": "crusher01",
  "password": "secret123"
}
```

**200 OK**

```json
{
  "token": "1|a1b2c3d4e5...",
  "user": {
    "id": 3,
    "name": "Tawanda Moyo",
    "username": "crusher01",
    "email": "crusher01@example.com",
    "role": "Crusher operator",
    "created_at": "2026-07-31T10:00:00.000000Z",
    "updated_at": "2026-07-31T10:00:00.000000Z"
  }
}
```

- Bad credentials → `422` with `errors.username: ["The provided credentials are incorrect."]`.
- Rate limited to **5 attempts / minute** per username+IP; further attempts return `429`.
- Store the token securely (react-native-keychain / SecureStore). It is used until logout or server-side revocation.

### 2.2 Current user — `GET /user`

Returns the `user` object from above. Useful to validate a stored token on app start.

### 2.3 Logout — `POST /logout`

Revokes the current token. Returns `{ "message": "Logged out successfully." }`. After this the token is dead — discard it locally.

---

## 3. Reference data

### 3.1 Users — `/users`

| Method | Path | Description |
| --- | --- | --- |
| GET | `/users` | List (ordered by name; optional `?search=` filters by partial username) |
| POST | `/users` | Create |
| GET | `/users/{user}` | Show one |
| PATCH | `/users/{user}` | Update (password optional on update) |
| DELETE | `/users/{user}` | Delete |

Create body:

```json
{
  "name": "Tawanda Moyo",
  "username": "crusher01",
  "email": "crusher01@example.com",
  "password": "secret123",
  "role": "Crusher operator"
}
```

`role` is one of: `Admin`, `Stock controller`, `Crusher operator`, `Stock receiver`, `Palletizing operator`, `Supervisor`.

### 3.2 Materials — `/materials`

| Method | Path | Description |
| --- | --- | --- |
| GET | `/materials` | List (ordered by code) |
| POST | `/materials` | Create |
| PATCH | `/materials/{material}` | Update |

Create/update body:

```json
{ "code": "HDPE-CLEAR", "name": "Clear HDPE flakes" }
```

`code` is unique. PATCH accepts either field alone.

---

## 4. Workflow endpoints

All seven modules share the same shape: `GET` lists newest-first as
`{ "data": [ ... ] }`, `POST` creates and returns the created record directly.
List responses include the `material` (code) key on the modules that reference a
material (intakes, crushing, dispatches, palletizing receipts) plus `recorded_by`
(name) on all modules (relations eager-loaded); create responses return only the
stored ids such as `material_id` and `recorded_by_user_id`.

### 4.1 Material intakes — `/material-intakes`

```json
{
  "date": "2026-08-01",
  "grn_number": "GRN-1001",
  "buyer_name": "EcoScrap Supplies",
  "material_code": "HDPE-CLEAR",
  "gross_weight_kg": 1050,
  "tare_weight_kg": 250,
  "unit_price": 0.85
}
```

Server returns (calculated: `net_weight_kg = gross - tare`, `total_value = net × price`):

```json
{
  "id": 12,
  "date": "2026-08-01",
  "grn_number": "GRN-1001",
  "buyer_name": "EcoScrap Supplies",
  "material_id": 2,
  "gross_weight_kg": 1050,
  "tare_weight_kg": 250,
  "net_weight_kg": 800,
  "unit_price": 0.85,
  "total_value": 680,
  "recorded_by_user_id": 3,
  "lock_version": 1,
  "created_at": "2026-08-01T08:15:00.000000Z",
  "updated_at": "2026-08-01T08:15:00.000000Z",
  "deleted_at": null
}
```

### 4.2 Crushing productions — `/crushing-productions`

```json
{
  "date": "2026-08-01",
  "batch_number": "CP-442",
  "material_intake_id": 12,
  "material_code": "HDPE-CLEAR",
  "input_weight_kg": 800,
  "output_chips_kg": 720
}
```

Server calculates `loss_kg = input - output` and `loss_percentage = loss / input`.

### 4.3 Dispatches — `/dispatches`

```json
{
  "date": "2026-08-01",
  "dispatch_note_number": "DN-330",
  "crushing_production_id": 20,
  "material_code": "HDPE-CLEAR",
  "weight_dispatched_kg": 700,
  "transported_by": "Hauler (John)"
}
```

### 4.4 Palletizing receipts — `/palletizing-receipts`

```json
{
  "date": "2026-08-01",
  "grn_number": "PR-77",
  "dispatch_id": 55,
  "material_code": "HDPE-CLEAR",
  "weight_received_kg": 680,
  "rate_per_kg": 0.9
}
```

Server calculates `amount_payable = weight_received × rate_per_kg`.

### 4.5 Palletizing productions — `/palletizing-productions`

```json
{
  "date": "2026-08-01",
  "batch_number": "PP-120",
  "palletizing_receipt_id": 9,
  "chips_input_kg": 680,
  "pellets_output_kg": 650
}
```

Server calculates `loss_kg` and `loss_percentage`.

### 4.6 Pellet sales — `/pellet-sales`

```json
{
  "date": "2026-08-01",
  "receipt_number": "R-9001",
  "customer_name": "PlastCo",
  "kg_sold": 500,
  "unit_price": 2.4
}
```

Server calculates `amount_received = kg_sold × unit_price`.

### 4.7 Cash remittances — `/cash-remittances`

```json
{
  "date": "2026-08-01",
  "voucher_number": "V-14",
  "period_covered": "July 2026",
  "chips_delivered_kg": 5000,
  "recovery_price_per_kg": 0.85,
  "sales_revenue": 2600,
  "cash_remitted": 2400
}
```

Server calculates `max_remittance_due` and `balance_retained`.

---

## 5. Dashboard — `GET /dashboard`

Optional query params: `from` and `to` (YYYY-MM-DD). Without them the whole
period is aggregated. Response is wrapped in `data`:

```json
{
  "data": {
    "material_purchased_kg": 12500,
    "chips_produced_kg": 11200,
    "crushing_loss_percentage": 0.104,
    "chips_dispatched_kg": 9000,
    "chips_on_hand_kg": 2200,
    "chips_received_kg": 8700,
    "receiving_variance_kg": 300,
    "payable_to_crushing": 7830,
    "pellets_produced_kg": 8300,
    "palletizing_loss_percentage": 0.046,
    "pellets_sold_kg": 6400,
    "finished_stock_kg": 1900,
    "sales_revenue": 15360,
    "cash_remitted": 14200,
    "balance_retained": 1160,
    "cash_collection_gap": 1160,
    "outstanding_to_crushing": -6370,
    "reconciliation_status": "balanced"
  }
}
```

`reconciliation_status` is `balanced` when `sales_revenue − cash_remitted ≤ balance_retained`, else `shortfall`.

---

## 5.1 Reports — `/reports/*`

Four read-only summary endpoints (same `from`/`to` query params as the
dashboard). All responses are wrapped in `data`:

| Endpoint | Purpose |
| --- | --- |
| `GET /reports/stock` | Material flow with a per-material breakdown |
| `GET /reports/production` | Crushing + palletizing performance, per-material and monthly |
| `GET /reports/sales` | Sales totals, per-customer and monthly |
| `GET /reports/cash-reconciliation` | Remittances vs revenue with a per-voucher view |

Example — `GET /reports/stock?from=2026-08-01&to=2026-08-31`:

```json
{
  "data": {
    "period": { "from": "2026-08-01", "to": "2026-08-31" },
    "totals": {
      "material_purchased_kg": 12500,
      "chips_produced_kg": 11200,
      "chips_dispatched_kg": 9000,
      "chips_on_hand_kg": 2200,
      "chips_received_kg": 8700,
      "receiving_variance_kg": 300,
      "pellets_produced_kg": 8300,
      "pellets_sold_kg": 6400,
      "finished_stock_kg": 1900
    },
    "per_material": [
      {
        "material_code": "PP",
        "material_name": "Polypropylene",
        "purchased_kg": 12500,
        "produced_kg": 11200,
        "dispatched_kg": 9000,
        "received_kg": 8700,
        "on_hand_kg": 2200
      }
    ]
  }
}
```

Notes:

- `per_material` only includes materials with activity in the period.
- `production` returns `crushing` and `palletizing` blocks with `input_kg`,
  `output_kg`, `loss_kg`, `loss_percentage`, plus `monthly` (keyed `YYYY-MM`)
  and `per_material` arrays.
- `sales` returns `totals` (`kg_sold`, `revenue`, `average_price_per_kg`,
  `transactions`) plus `per_customer` and `monthly` arrays.
- `cash-reconciliation` returns `totals` mirroring the dashboard (including
  `reconciliation_status`) plus a `remittances` array of individual vouchers.
- These are read-only; nothing is written when you call them.

---

## 6. Offline sync (doc 05)

Syncable tables (8): `materials`, `material_intakes`, `crushing_productions`,
`dispatches`, `palletizing_receipts`, `palletizing_productions`,
`pellet_sales`, `cash_remittances`.

Every syncable row carries:
- **`lock_version`** — optimistic-lock counter. Starts at 1, +1 on every accepted update/delete. **This is the conflict-detection key.**
- **`deleted_at`** — rows are soft-deleted, so tombstones sync down to the device.

### 6.1 Pull — `GET /sync/pull?since=<ISO-8601>`

Returns everything changed after `since`, grouped by table:

```json
{
  "server_time": "2026-08-01T10:00:00.000000Z",
  "changes": {
    "pellet_sales": [ { ...PelletSale } ],
    "cash_remittances": []
  },
  "deleted": {
    "pellet_sales": [ { ...PelletSale with deleted_at set } ]
  }
}
```

- **Full sync:** omit `since` (first run). Save `server_time`.
- **Incremental:** send the last `server_time` as `since`. Rows with `updated_at > since` come back in `changes` (live) or `deleted` (soft-deleted).
- Apply order matters: upsert `changes` first, then mark/delete the ids in `deleted` (or just upsert both — `deleted_at` tells you the state).

### 6.2 Push — `POST /sync/push`

Send all pending offline changes grouped by table:

```json
{
  "changes": {
    "pellet_sales": [
      {
        "local_id": "uuid-1",
        "server_id": null,
        "server_lock_version": null,
        "deleted": false,
        "data": {
          "date": "2026-08-01",
          "receipt_number": "R-9001",
          "customer_name": "PlastCo",
          "kg_sold": 500,
          "unit_price": 2.4
        }
      },
      {
        "local_id": "uuid-2",
        "server_id": 31,
        "server_lock_version": 1,
        "deleted": false,
        "data": { "kg_sold": 480 }
      },
      {
        "local_id": "uuid-3",
        "server_id": 33,
        "server_lock_version": 2,
        "deleted": true,
        "data": {}
      }
    ]
  }
}
```

Rules per change:

| Field | Meaning |
| --- | --- |
| `local_id` | Your client id — echoed back so you can correlate results |
| `server_id` | `null` to create; the server id to update/delete |
| `server_lock_version` | The `lock_version` you last pulled for this record (or `null` for new) |
| `deleted` | `true` soft-deletes the server record |
| `data` | Fields to create or merge. Partial data on updates is merged with server state — derived fields are then recalculated server-side |

Response:

```json
{
  "accepted": [
    {
      "table": "pellet_sales",
      "local_id": "uuid-1",
      "status": "accepted",
      "server_id": 41,
      "lock_version": 1
    },
    {
      "table": "pellet_sales",
      "local_id": "uuid-2",
      "status": "accepted",
      "server_id": 31,
      "lock_version": 2
    },
    {
      "table": "pellet_sales",
      "local_id": "uuid-3",
      "status": "accepted",
      "server_id": 33,
      "lock_version": 3,
      "deleted": true
    }
  ],
  "conflicts": [],
  "rejected": []
}
```

- **`accepted`** → save `server_id` + `lock_version` against `local_id`, drop from the outbox.
- **`conflicts`** → your `server_lock_version` was stale; nothing was written. A `conflict_id` is stored server-side for admin review (Filament → Sync Conflicts). Pull and re-apply on top of the fresh version, or surface to the user.
- **`rejected`** → validation failed (bad data) or the server record no longer exists. Keep in the outbox, fix, retry.

> **Idempotency note:** creating a new record uses `local_id` only for your bookkeeping — the server does not dedupe by `local_id`, so if a push times out **do not blindly retry the same create** without knowing whether it landed. Pull first (you'll see the new `server_id`), or let the user re-submit after confirming.

### 6.3 Recommended sync flow

```
On app start / when online:
  token = load token
  if token invalid → force login screen

  pull = GET /sync/pull?since=lastPulledAt
  apply pull.changes  (upsert locally by id)
  apply pull.deleted  (mark deleted locally)
  lastPulledAt = pull.server_time

  while outbox not empty:
    push = POST /sync/push { changes: outbox }
    for each accepted:   outbox[local_id].server_id/lock_version = result; remove
    for each rejected:   keep, log errors, surface fixable ones
    for each conflict:   remove from outbox, queue for re-pull + manual review
```

---

## 7. Mobile integration checklist

- [ ] Store token in SecureStore; attach `Authorization: Bearer <token>` to every request.
- [ ] On 401 → clear token → login screen.
- [ ] Never send derived/calculated fields; read them from responses.
- [ ] Capture `lock_version` from every syncable record; send it back on updates.
- [ ] Keep a per-record outbox (`local_id`, `server_id`, `server_lock_version`, `deleted`, `data`, status) so partial syncs are safe.
- [ ] Generate `local_id` with a UUID library; persist it until the push is accepted.
- [ ] Treat `conflicts` as user-visible: show "This record was changed on the server" and offer to re-pull or discard.
- [ ] Rate-limit awareness: login throttled at 5/min; show a friendly message on `429`.

## 8. Quick curl sanity check

```bash
BASE=https://plastic-recycling.test/api

# Login
TOKEN=$(curl -s -X POST $BASE/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"crusher01","password":"secret123"}' | jq -r .token)

# Authed call
curl -s $BASE/materials -H "Authorization: Bearer $TOKEN"

# Push an offline sale
curl -s -X POST $BASE/sync/push -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"changes":{"pellet_sales":[{"local_id":"demo-1","server_id":null,"server_lock_version":null,"deleted":false,"data":{"date":"2026-08-01","receipt_number":"R-9999","customer_name":"PlastCo","kg_sold":10,"unit_price":2.0}}]}}'

# Incremental pull
curl -s "$BASE/sync/pull?since=2026-08-01T00:00:00Z" -H "Authorization: Bearer $TOKEN"
```
