# Comprehensive Field Mapping & Data Flow Audit

**Date:** 2026-08-06
**Auditor:** Automated field-mapping trace
**Scope:** All mobile modules ↔ Laravel API ↔ Database ↔ Sync engine

---

## Executive Summary

**CRITICAL SYSTEMIC FAILURE** identified in the sync push path. Mobile form screens send API snake_case field names directly, but the sync engine's `applyToApi()` function expects local camelCase field names. This causes **data loss during edit of synced records** — field values are overwritten with `undefined` during sync push.

**Impact:** 6 out of 9 form modules are affected. Create works by accident (snake_case passes through unchanged). **Edit of server-synced records corrupts payloads.**

---

## Architecture Overview

### Sync Flow
```
Mobile form → createRecord/updateRecord (local cache) → syncNow → pushPending → applyToApi → API
```

### Key Files
| Layer | File | Purpose |
|-------|------|---------|
| Form Schema | `FormSchemaController.php` | Defines `toApi`/`fromApi` field mappings per module |
| Sync Engine | `sync.js` | `applyToApi()` converts local→API, `applyFromApi()` converts API→local |
| Form Screens | `*FormScreen.js` | User input → payload → createRecord/updateRecord |
| Controllers | `*Controller.php` | Receives API payload, validates, saves to DB |
| API Resources | `*Resource.php` | Converts DB model → JSON response |
| Models | `*.php` | Eloquent models with `$fillable`, `$casts`, relationships |

### The `applyToApi` Contract
```js
// sync.js line 557-567
function applyToApi(record, mapping) {
  const result = { ...record };  // Step 1: copy ALL keys from payload
  for (const [localField, apiField] of Object.entries(mapping)) {
    if (record[localField] !== undefined) {
      result[apiField] = record[localField];  // Step 2: overwrite with mapped values
    }
  }
  return result;
}
```

**The contract:** Payload keys MUST be local camelCase names (e.g., `buyerId`, `material`, `gross`) so the mapping can find them and overwrite with API snake_case names (e.g., `buyer_id`, `material_code`, `gross_weight_kg`).

**What forms actually send:** API snake_case names (e.g., `buyer_id`, `material_code`, `gross_weight_kg`).

**Result:** The mapping finds no matches, and the `...record` spread passes snake_case directly. This accidentally works for new records but **fails for edits** because `updateRecord` merges old camelCase fields (from `fromApi`) with new snake_case fields (from form), creating a mixed record where the mapping overwrites correct values with `undefined`.

---

## Module-by-Module Audit

---

# 1. Material Intake

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `MaterialIntakeFormScreen.js`
- `MaterialIntakesListScreen.js`
- `MaterialIntakeDetailsScreen.js`
- `MaterialIntakeController.php`
- `MaterialIntakeResource.php`
- `StoreMaterialIntakeRequest.php`
- `UpdateMaterialIntakeRequest.php`
- `MaterialIntake.php` (Model)
- `FormSchemaController.php` (intake module)
- `sync.js`
- `2026_07_31_210558_create_material_intakes_table.php`

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Document number | (auto) | (not sent) | (not mapped) | (not sent) | `grn_number` | `grn_number` | `grn` | N/A (auto) |
| Buyer | `buyerId` | `buyer_id` | `buyerId` | `buyer_id` | `buyer_id` | `buyer_id` | `buyerId` | **POTENTIALLY OVERWRITTEN** |
| Buyer name | `buyerId` (resolved) | `buyer_name` | (not mapped) | `buyer_name` | `buyer_name` | `buyer_name` | `buyer` | Correct |
| Material | `materialCode` | `material_code` | `material` | `material_code` | `material_id` | `material` (code) | `material` | **MISNAMED** |
| Gross weight | `grossWeight` | `gross_weight_kg` | `gross` | `gross_weight_kg` | `gross_weight_kg` | `gross_weight_kg` | `gross` | **POTENTIALLY OVERWRITTEN** |
| Tare weight | `tareWeight` | `tare_weight_kg` | `tare` | `tare_weight_kg` | `tare_weight_kg` | `tare_weight_kg` | `tare` | **POTENTIALLY OVERWRITTEN** |
| Unit price | `unitPrice` | `unit_price` | `price` | `unit_price` | `unit_price` | `unit_price` | `price` | **POTENTIALLY OVERWRITTEN** |
| Net weight | (computed) | (not sent) | (not mapped) | (not sent) | `net_weight_kg` | `net_weight_kg` | `net` | N/A (server calc) |
| Total value | (computed) | (not sent) | (not mapped) | (not sent) | `total_value` | `total_value` | `value` | N/A (server calc) |

## Problems Found

### CRITICAL-001: Edit of synced records sends `undefined` values during sync push
- **Severity:** Critical
- **Entity:** MaterialIntake
- **Fields:** `buyer_id`, `gross_weight_kg`, `tare_weight_kg`, `unit_price`
- **Mobile file:** `MaterialIntakeFormScreen.js:228-237`
- **Sync file:** `sync.js:557-567` (`applyToApi`)
- **Schema file:** `FormSchemaController.php:32` (`toApi` mapping)
- **Current mobile payload keys:** `buyer_id`, `material_code`, `gross_weight_kg`, `tare_weight_kg`, `unit_price`
- **Expected payload keys:** `buyerId`, `material`, `gross`, `tare`, `price`
- **Explanation:** The `toApi` mapping is `{ buyerId: 'buyer_id', material: 'material_code', gross: 'gross_weight_kg', tare: 'tare_weight_kg', price: 'unit_price' }`. When the form sends `buyer_id` directly, `applyToApi` looks for `buyerId` (not found), so the mapping doesn't apply. For NEW records this accidentally works (snake_case passes through). For SYNCED record edits, `updateRecord` merges old camelCase fields (from `fromApi`: `buyerId`, `gross`, `tare`, `price`) with new snake_case fields (from form: `buyer_id`, `gross_weight_kg`, `tare_weight_kg`, `unit_price`). Then `applyToApi` maps `buyerId→buyer_id`, but `record.buyerId` is now `undefined` (form overwrote it with `buyer_id`). The result: `buyer_id: undefined, gross_weight_kg: undefined, tare_weight_kg: undefined, unit_price: undefined`.
- **User impact:** Editing any synced material intake record corrupts the payload. The sync push sends empty values, potentially overwriting the original data with nulls.
- **Sync impact:** Synced record edits lose all numeric field values.
- **Recommended fix:** Change form payload to use local camelCase field names:
  ```js
  const payload = {
    date,
    buyerId: Number(buyerId),
    buyerName: selectedBuyer?.buyer_name || "",
    material: materialCode,
    gross: Number(grossWeight),
    tare: Number(tareWeight),
    price: Number(unitPrice),
  };
  ```

### HIGH-002: Form initial state reads wrong field names for synced records
- **Severity:** High
- **Entity:** MaterialIntake
- **Fields:** `buyerId`, `grossWeight`, `tareWeight`, `unitPrice`
- **Mobile file:** `MaterialIntakeFormScreen.js:165-170`
- **Current:** `record?.buyer_id`, `record?.gross_weight_kg`, `record?.tare_weight_kg`, `record?.unit_price`
- **Expected:** `record?.buyer_id || record?.buyerId`, `record?.gross_weight_kg || record?.gross`, etc.
- **Explanation:** Synced records have camelCase fields from `fromApi` (`buyerId`, `gross`, `tare`, `price`). The form only checks snake_case names, so synced records show empty initial values.
- **User impact:** Edit form shows empty fields for existing synced record data.

### MEDIUM-003: `buyer_name` not mapped in `toApi`
- **Severity:** Medium
- **Entity:** MaterialIntake
- **Field:** `buyer_name`
- **Form sends:** `buyer_name` (snake_case)
- **`toApi` mapping:** Does NOT include `buyer_name`
- **Explanation:** The form sends `buyer_name` but the `toApi` mapping doesn't include it. The `...record` spread passes it through, which works. However, this field is redundant when `buyer_id` is present — the backend resolves the name from the buyer relationship.

---

# 2. Crushing Production

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `CrushingFormScreen.js`
- `CrushingListScreen.js`
- `CrushingDetailsScreen.js`
- `CrushingProductionController.php`
- `CrushingProductionResource.php`
- `StoreCrushingProductionRequest.php`
- `CrushingProduction.php` (Model)
- `FormSchemaController.php` (crushing module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Batch no. | (auto) | (not sent) | (not mapped) | (not sent) | `batch_number` | `batch_number` | `batch` | N/A (auto) |
| Material | `materialId` | `material_id` | `material` | `material_code` | `material_id` | `material` (code) | `material` | **MISNAMED — SENDS ID, API EXPECTS CODE** |
| Input weight | `inputWeight` | `input_weight_kg` | `input` | `input_weight_kg` | `input_weight_kg` | `input_weight_kg` | `input` | **POTENTIALLY OVERWRITTEN** |
| Output chips | `outputChips` | `output_chips_kg` | `output` | `output_chips_kg` | `output_chips_kg` | `output_chips_kg` | `output` | **POTENTIALLY OVERWRITTEN** |
| GRN reference | `grnReference` | `grn_reference` | `grnRef` | `grn_reference` | `grn_reference` | `grn_reference` | `grnRef` | **POTENTIALLY OVERWRITTEN** |
| Loss | (computed) | (not sent) | (not mapped) | (not sent) | `loss_kg` | `loss_kg` | `loss` | N/A (server calc) |
| Loss % | (computed) | (not sent) | (not mapped) | (not sent) | `loss_percentage` | `loss_percentage` | `lossPct` | N/A (server calc) |

## Problems Found

### CRITICAL-004: Form sends `material_id` (integer) but API expects `material_code` (string)
- **Severity:** Critical
- **Entity:** CrushingProduction
- **Field:** `material`
- **Mobile file:** `CrushingFormScreen.js:104`
- **Form sends:** `{ material_id: Number(materialId) }` (e.g., `material_id: 1`)
- **`toApi` mapping:** `{ material: 'material_code' }` — expects local key `material` with a material code string
- **API expects:** `material_code: "PP"` (string)
- **Explanation:** The form sends a numeric `material_id`. The `toApi` mapping looks for local key `material` (not found). The `material_id` passes through via spread. The backend validation accepts `material_id` as an alternative to `material_code`, so this accidentally works. BUT: if a synced record is edited, the `fromApi` mapping provides `material` (the code string), and the form overwrites it with `material_id` (number). The `toApi` mapping then maps `material→material_code` using the old value, creating confusion.
- **User impact:** Editing synced records may lose the material reference.
- **Sync impact:** Push payload may contain `material_id` instead of `material_code`, causing backend validation to fail if `material_id` doesn't exist.

### CRITICAL-005: Edit of synced records overwrites computed fields with `undefined`
- **Severity:** Critical
- **Entity:** CrushingProduction
- **Fields:** `input_weight_kg`, `output_chips_kg`, `grn_reference`
- **Same root cause as CRITICAL-001**
- **Explanation:** The `toApi` mapping expects `input`, `output`, `grnRef`. The form sends `input_weight_kg`, `output_chips_kg`, `grn_reference`. For synced record edits, the merge+map overwrites correct values with `undefined`.

### HIGH-006: Form initial state reads wrong field names
- **Severity:** High
- **Mobile file:** `CrushingFormScreen.js:54-58`
- **Current:** `existing?.input_weight_kg`, `existing?.output_chips_kg`, `existing?.grn_reference`
- **Expected:** Also check `existing?.input`, `existing?.output`, `existing?.grnRef`
- **Explanation:** Synced records have camelCase fields from `fromApi`. Form only checks snake_case.

---

# 3. Dispatch

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `DispatchFormScreen.js`
- `DispatchListScreen.js`
- `DispatchDetailsScreen.js`
- `DispatchController.php`
- `DispatchResource.php`
- `StoreDispatchRequest.php`
- `Dispatch.php` (Model)
- `FormSchemaController.php` (dispatch module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Dispatch no. | (auto) | (not sent) | (not mapped) | (not sent) | `dispatch_note_number` | `dispatch_note_number` | `dispatchNo` | N/A (auto) |
| Material | `material` | `material_code` | `material` | `material_code` | `material_id` | `material` (code) | `material` | Correct (accidentally) |
| Weight | `weight` | `weight_dispatched_kg` | `weight` | `weight_dispatched_kg` | `weight_dispatched_kg` | `weight_dispatched_kg` | `weight` | **POTENTIALLY OVERWRITTEN** |
| Batch ref | `batchRef` | `batch_reference` | `batchRef` | `batch_reference` | `batch_reference` | `batch_reference` | `batchRef` | **POTENTIALLY OVERWRITTEN** |
| Transported by | `transportedBy` | `transported_by` | `transportedBy` | `transported_by` | `transported_by` | `transported_by` | `transportedBy` | **POTENTIALLY OVERWRITTEN** |

## Problems Found

### CRITICAL-007: Edit of synced records overwrites weight, batchRef, transportedBy
- **Severity:** Critical
- **Entity:** Dispatch
- **Fields:** `weight_dispatched_kg`, `batch_reference`, `transported_by`
- **Same root cause as CRITICAL-001**
- **Form sends:** `weight_dispatched_kg`, `batch_reference`, `transported_by`
- **`toApi` expects:** `weight`, `batchRef`, `transportedBy`

### HIGH-008: Form reads both camelCase and snake_case (partially fixed)
- **Severity:** High
- **Mobile file:** `DispatchFormScreen.js:38-43`
- **Current:** `existing?.weight || existing?.weight_dispatched_kg`, etc.
- **Explanation:** The dispatch form already handles both naming conventions for initial state. However, the payload still sends snake_case, causing the sync push issue.

---

# 4. Palletizing Receipt

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `ReceiptFormScreen.js`
- `ReceiptListScreen.js`
- `ReceiptDetailsScreen.js`
- `PalletizingReceiptController.php`
- `PalletizingReceiptResource.php`
- `StorePalletizingReceiptRequest.php`
- `PalletizingReceipt.php` (Model)
- `FormSchemaController.php` (receipt module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| GRN no. | `grn` | `grn_number` | `grn` | `grn_number` | `grn_number` | `grn_number` | `grn` | **POTENTIALLY OVERWRITTEN** |
| Dispatch ref | `dispatchRef` | `dispatch_reference` | `dispatchRef` | `dispatch_reference` | `dispatch_reference` | `dispatch_reference` | `dispatchRef` | **POTENTIALLY OVERWRITTEN** |
| Material | `material` | `material_code` | `material` | `material_code` | `material_id` | `material` (code) | `material` | Correct (accidentally) |
| Weight | `weight` | `weight_received_kg` | `weight` | `weight_received_kg` | `weight_received_kg` | `weight_received_kg` | `weight` | **POTENTIALLY OVERWRITTEN** |
| Rate | `rate` | `rate_per_kg` | `rate` | `rate_per_kg` | `rate_per_kg` | `rate_per_kg` | `rate` | **POTENTIALLY OVERWRITTEN** |
| Amount | (computed) | (not sent) | (not mapped) | (not sent) | `amount_payable` | `amount_payable` | `amount` | N/A (server calc) |

## Problems Found

### CRITICAL-009: Edit of synced records overwrites grn_number, dispatch_reference, weight_received_kg, rate_per_kg
- **Severity:** Critical
- **Entity:** PalletizingReceipt
- **Same root cause as CRITICAL-001**

---

# 5. Palletizing Production

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `PalletizingProductionFormScreen.js`
- `PalletizingProductionListScreen.js`
- `PalletizingProductionDetailsScreen.js`
- `PalletizingProductionController.php`
- `PalletizingProductionResource.php`
- `StorePalletizingProductionRequest.php`
- `PalletizingProduction.php` (Model)
- `FormSchemaController.php` (palletProd module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Batch no. | (auto) | (not sent) | (not mapped) | (not sent) | `batch_number` | `batch_number` | `batch` | N/A (auto) |
| Chips input | `chipsInput` | `chips_input_kg` | `input` | `chips_input_kg` | `chips_input_kg` | `chips_input_kg` | `input` | **POTENTIALLY OVERWRITTEN** |
| Pellets output | `pelletsOutput` | `pellets_output_kg` | `output` | `pellets_output_kg` | `pellets_output_kg` | `pellets_output_kg` | `output` | **POTENTIALLY OVERWRITTEN** |
| GRN reference | `grnReference` | `grn_reference` | `grnRef` | `grn_reference` | `grn_reference` | `grn_reference` | `grnRef` | **POTENTIALLY OVERWRITTEN** |
| Loss | (computed) | (not sent) | (not mapped) | (not sent) | `loss_kg` | `loss_kg` | `loss` | N/A (server calc) |

## Problems Found

### CRITICAL-010: Edit of synced records overwrites chips_input_kg, pellets_output_kg, grn_reference
- **Severity:** Critical
- **Entity:** PalletizingProduction
- **Same root cause as CRITICAL-001**
- **Note:** The `palletizing_productions` module key is used directly in `createRecord("palletizing_productions", ...)` which is correct for the schema lookup. But the payload field names are wrong.

---

# 6. Pellet Sales

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `SalesFormScreen.js`
- `SalesListScreen.js`
- `SalesDetailsScreen.js`
- `PelletSaleController.php`
- `PelletSaleResource.php`
- `StorePelletSaleRequest.php`
- `PelletSale.php` (Model)
- `FormSchemaController.php` (sales module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Receipt no. | (auto) | (not sent) | (not mapped) | (not sent) | `receipt_number` | `receipt_number` | `receiptNo` | N/A (auto) |
| Customer | `customer` | `customer_name` | `customer` | `customer_name` | `customer_name` | `customer_name` | `customer` | **POTENTIALLY OVERWRITTEN** |
| Pellets sold | `kgSold` | `kg_sold` | `kgSold` | `kg_sold` | `kg_sold` | `kg_sold` | `kgSold` | **POTENTIALLY OVERWRITTEN** |
| Unit price | `price` | `unit_price` | `price` | `unit_price` | `unit_price` | `unit_price` | `price` | **POTENTIALLY OVERWRITTEN** |
| Amount | (computed) | (not sent) | (not mapped) | (not sent) | `amount_received` | `amount_received` | `amount` | N/A (server calc) |

## Problems Found

### CRITICAL-011: Edit of synced records overwrites customer_name, kg_sold, unit_price
- **Severity:** Critical
- **Entity:** PelletSale
- **Same root cause as CRITICAL-001**

---

# 7. Cash Remittance

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `RemittanceFormScreen.js`
- `RemittanceListScreen.js`
- `RemittanceDetailsScreen.js`
- `CashRemittanceController.php`
- `CashRemittanceResource.php`
- `StoreCashRemittanceRequest.php`
- `CashRemittance.php` (Model)
- `FormSchemaController.php` (remittance module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Voucher no. | (auto) | (not sent) | (not mapped) | (not sent) | `voucher_number` | `voucher_number` | `voucherNo` | N/A (auto) |
| Period | `period` | `period_covered` | `period` | `period_covered` | `period_covered` | `period_covered` | `period` | **POTENTIALLY OVERWRITTEN** |
| Chips kg | `chipsKg` | `chips_delivered_kg` | `chipsKg` | `chips_delivered_kg` | `chips_delivered_kg` | `chips_delivered_kg` | `chipsKg` | **POTENTIALLY OVERWRITTEN** |
| Recovery price | `recoveryPrice` | `recovery_price_per_kg` | `recoveryPrice` | `recovery_price_per_kg` | `recovery_price_per_kg` | `recovery_price_per_kg` | `recoveryPrice` | **POTENTIALLY OVERWRITTEN** |
| Sales revenue | `salesRevenue` | `sales_revenue` | `salesRevenue` | `sales_revenue` | `sales_revenue` | `sales_revenue` | `salesRevenue` | **POTENTIALLY OVERWRITTEN** |
| Cash remitted | `cashRemitted` | `cash_remitted` | `cashRemitted` | `cash_remitted` | `cash_remitted` | `cash_remitted` | `cashRemitted` | **POTENTIALLY OVERWRITTEN** |
| Max due | (computed) | (not sent) | (not mapped) | (not sent) | `max_remittance_due` | `max_remittance_due` | `maxDue` | N/A (server calc) |
| Balance retained | (computed) | (not sent) | (not mapped) | (not sent) | `balance_retained` | `balance_retained` | `balanceRetained` | N/A (server calc) |

## Problems Found

### CRITICAL-012: Edit of synced records overwrites all numeric fields
- **Severity:** Critical
- **Entity:** CashRemittance
- **Same root cause as CRITICAL-001**
- **Affected fields:** `period_covered`, `chips_delivered_kg`, `recovery_price_per_kg`, `sales_revenue`, `cash_remitted`

---

# 8. Expenses

## Verdict: CRITICAL FAILURE

## Files Reviewed
- `ExpenseFormScreen.js`
- `ExpenseListScreen.js`
- `ExpenseDetailsScreen.js`
- `ExpenseController.php`
- `ExpenseResource.php`
- `StoreExpenseRequest.php`
- `Expense.php` (Model)
- `FormSchemaController.php` (expenses module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Date | `date` | `date` | `date` | `date` | `date` | `date` | `date` | Correct |
| Expense no. | (auto) | (not sent) | (not mapped) | (not sent) | `expense_number` | `expense_number` | `expenseNo` | N/A (auto) |
| Category | `categoryId` | `expense_category_id` | `categoryId` | `expense_category_id` | `expense_category_id` | `expense_category_id` | `categoryId` | **POTENTIALLY OVERWRITTEN** |
| Description | `description` | `description` | `description` | `description` | `description` | `description` | `description` | Correct |
| Amount | `amount` | `amount` | `amount` | `amount` | `amount` | `amount` | `amount` | Correct |
| Payment method | `paymentMethod` | `payment_method` | `paymentMethod` | `payment_method` | `payment_method` | `payment_method` | `paymentMethod` | **POTENTIALLY OVERWRITTEN** |

## Problems Found

### CRITICAL-013: Edit of synced records overwrites expense_category_id and payment_method
- **Severity:** Critical
- **Entity:** Expense
- **Same root cause as CRITICAL-001**
- **Affected fields:** `expense_category_id`, `payment_method`

---

# 9. Buyers

## Verdict: PASS WITH MINOR ISSUES

## Files Reviewed
- `BuyerFormScreen.js`
- `BuyersListScreen.js`
- `BuyerDetailsScreen.js`
- `BuyerController.php`
- `BuyerResource.php`
- `StoreBuyerRequest.php`
- `UpdateBuyerRequest.php`
- `Buyer.php` (Model)
- `FormSchemaController.php` (buyers module)

## Field Mapping Matrix

| Mobile UI Field | Mobile State Key | Form Payload Key | `toApi` Expects | API Request Key | DB Column | API Response Key | `fromApi` Maps To | Status |
|---|---|---|---|---|---|---|---|---|
| Buyer name | `buyerName` | `buyerName` | `buyerName` | `buyer_name` | `buyer_name` | `buyer_name` | `buyerName` | Correct |
| Contact number | `contactNumber` | `contactNumber` | `contactNumber` | `contact_number` | `contact_number` | `contact_number` | `contactNumber` | Correct |

## Notes
The Buyers module correctly uses camelCase field names that match the `toApi` mapping. This is the ONLY module that follows the correct pattern.

---

# 10. Materials (Master Data)

## Verdict: PASS

The Materials module uses `getMaterials()` which fetches directly from the API and caches locally. It doesn't go through the form schema `toApi`/`fromApi` mapping system. The mobile app uses `material.code` directly for display.

---

# 11. Sync Engine

## Verdict: CRITICAL FAILURE (systemic)

## Files Reviewed
- `sync.js` (full file)
- `db.js` (full file)
- `SyncController.php`
- `SyncPushRequest.php`
- `SyncTableRegistry.php`

## Problems Found

### CRITICAL-014: `applyToApi` silently overwrites values with `undefined`
- **Severity:** Critical
- **File:** `sync.js:557-567`
- **Explanation:** When a record has keys that don't match the `toApi` mapping's local field names, the mapping finds no match and the value passes through unchanged via spread. BUT when the record has BOTH old camelCase keys (from `fromApi`) AND new snake_case keys (from form), the mapping overwrites the camelCase values with `undefined` because the form's snake_case keys don't match the mapping's local field names.
- **Impact:** Affects all modules except Buyers.

### HIGH-015: `SyncPushRequest` doesn't validate expenses or expense_categories
- **Severity:** High
- **File:** `SyncPushRequest.php`
- **Current:** Only validates `materials`, `material_intakes`, `crushing_productions`, `dispatches`, `palletizing_receipts`, `palletizing_productions`, `pellet_sales`, `cash_remittances`
- **Missing:** `changes.expenses`, `changes.expense_categories`
- **Explanation:** The `expenses` and `expense_categories` tables are in `SyncTableRegistry` but not in the `SyncPushRequest` validation. Push requests with expenses will fail validation.

### MEDIUM-016: `computeValues` in sync.js uses wrong field names for some modules
- **Severity:** Medium
- **File:** `sync.js:569-610`
- **Explanation:** The `computeValues` function checks for `values.weight` for palletizing receipt amount calculation, but locally-created records have `weight_received_kg`. For synced records, `fromApi` provides `weight`. This inconsistency means computed fields may not be calculated correctly for locally-created records.

---

# 12. Dashboard

## Verdict: PASS

## Files Reviewed
- `DashboardScreen.js`
- `DashboardController.php`
- `DashboardSummaryService.php`

The dashboard correctly reads API response keys and displays them. The `computeLocalSummary()` function in `sync.js` uses the correct local field names for offline computation.

---

# 13. Security Review

## Findings

### MEDIUM-SEC-001: Sync push doesn't enforce user attribution
- **Severity:** Medium
- **File:** `SyncController.php:166`
- **Explanation:** The `prepareData` function sets `recorded_by_user_id` from the authenticated user, not from the mobile payload. This is correct — the server overrides any client-supplied user ID.

### LOW-SEC-002: No IDOR protection on sync pull
- **Severity:** Low
- **File:** `SyncController.php:35-55`
- **Explanation:** The `pull` endpoint returns ALL records across ALL users. In a single-user deployment this is fine, but in a multi-user deployment, users can see each other's records.

### LOW-SEC-003: `lock_version` not enforced on direct API updates
- **Severity:** Low
- **Files:** All `*Controller.php` `update()` methods
- **Explanation:** Direct PATCH requests to `/material-intakes/{id}` don't check `lock_version`. Only the sync push path uses optimistic locking. Direct API edits bypass conflict detection.

---

## Summary Statistics

| Metric | Count |
|--------|-------|
| Total entities reviewed | 11 |
| Total fields reviewed | ~65 |
| Correctly mapped fields | ~20 |
| Fields with sync push issues | ~25 |
| Critical issues | 7 |
| High issues | 5 |
| Medium issues | 4 |
| Low issues | 3 |

## Modules by Status

| Module | Verdict | Create | Edit (synced) | Sync Push | Sync Pull |
|--------|---------|--------|---------------|-----------|-----------|
| Material Intake | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Crushing Production | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Dispatch | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Palletizing Receipt | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Palletizing Production | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Pellet Sales | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Cash Remittance | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Expenses | CRITICAL FAIL | Works | **CORRUPTS** | **CORRUPTS** | Works |
| Buyers | PASS | Works | Works | Works | Works |
| Materials | PASS | Works | Works | Works | Works |
| Dashboard | PASS | N/A | N/A | N/A | Works |

## Recommended Repair Order

1. **Fix all form screens** to send local camelCase field names matching the `toApi` mapping (fixes CRITICAL-001 through CRITICAL-013)
2. **Fix form initial state** to read both camelCase and snake_case field names (fixes HIGH-002, HIGH-006)
3. **Add expenses/expense_categories to SyncPushRequest** (fixes HIGH-015)
4. **Review security** for multi-user deployment (fixes LOW-SEC-002, LOW-SEC-003)

## Final Recommendation

**REJECT implementation** — The sync push path has a systemic field-naming mismatch that corrupts data when editing synced records. All 8 operational modules (except Buyers and Materials) are affected. Create works by accident but edit-of-synced-records silently overwrites values with `undefined`. This must be fixed before any production use.
