# Mobile App Audit — Missing Pages & Endpoints

## Summary

| Module | List | Form | Details | API show() | Action |
|--------|------|------|---------|------------|--------|
| Buyers | ✅ | ✅ | ✅ | ❌ | Add show() endpoint |
| Crushing | ✅ | ✅ | ✅ | ❌ | Add show() endpoint |
| MaterialIntakes | ✅ | ✅ | ✅ | ❌ | Add show() endpoint |
| Materials | ✅ | ✅ | ❌ | ❌ | Add details screen + show() endpoint |
| Dispatch | ✅ | ✅ | ❌ | ❌ | Add details screen + show() endpoint |
| Receipt | ✅ | ✅ | ❌ | ❌ | Add details screen + show() endpoint |
| Sales | ✅ | ✅ | ❌ | ❌ | Add details screen + show() endpoint |
| Remittance | ✅ | ✅ | ❌ | ❌ | Add details screen + show() endpoint |
| Users | ✅ | N/A | ✅ | ✅ | Complete |

---

## Tasks

### 1. Add show() endpoints (Backend)
- [ ] MaterialIntakeController — add show()
- [ ] CrushingProductionController — add show()
- [ ] DispatchController — add show()
- [ ] PalletizingReceiptController — add show()
- [ ] PalletizingProductionController — add show()
- [ ] PelletSaleController — add show()
- [ ] CashRemittanceController — add show()
- [ ] MaterialController — add show()
- [ ] BuyerController — add show()
- [ ] Register GET routes for all show() endpoints

### 2. Add missing Details screens (Mobile)
- [ ] MaterialsDetailsScreen
- [ ] DispatchDetailsScreen
- [ ] ReceiptDetailsScreen
- [ ] SalesDetailsScreen
- [ ] RemittanceDetailsScreen

### 3. Update navigation (Mobile)
- [ ] Register all details screens in App.js stack navigators
- [ ] Add tap-to-view on list cards for missing modules

### 4. Update API docs
- [ ] Add show() endpoints to openapi.yaml

---

## Module Details

### Materials — Missing: Details screen + show() endpoint
- List: ✅ MaterialsListScreen
- Form: ✅ MaterialFormScreen
- Details: ❌ No details screen
- API: ❌ No show() endpoint
- Fields: id, code, name, lock_version

### Dispatch — Missing: Details screen + show() endpoint
- List: ✅ DispatchListScreen
- Form: ✅ DispatchFormScreen
- Details: ❌ No details screen
- API: ❌ No show() endpoint
- Fields: id, date, dispatch_note_number, batch_reference, material, weight_dispatched_kg, transported_by

### Receipt — Missing: Details screen + show() endpoint
- List: ✅ ReceiptListScreen
- Form: ✅ ReceiptFormScreen
- Details: ❌ No details screen
- API: ❌ No show() endpoint
- Fields: id, date, grn_number, dispatch_reference, material, weight_received_kg, rate_per_kg, amount_payable

### Sales — Missing: Details screen + show() endpoint
- List: ✅ SalesListScreen
- Form: ✅ SalesFormScreen
- Details: ❌ No details screen
- API: ❌ No show() endpoint
- Fields: id, date, receipt_number, customer_name, kg_sold, unit_price, amount_received

### Remittance — Missing: Details screen + show() endpoint
- List: ✅ RemittanceListScreen
- Form: ✅ RemittanceFormScreen
- Details: ❌ No details screen
- API: ❌ No show() endpoint
- Fields: id, date, voucher_number, period_covered, chips_delivered_kg, recovery_price_per_kg, sales_revenue, cash_remitted, max_remittance_due, balance_retained

### Buyers — Missing: show() endpoint only
- List: ✅ BuyersListScreen
- Form: ✅ BuyerFormScreen
- Details: ✅ BuyerDetailsScreen
- API: ❌ No show() endpoint

### Crushing — Missing: show() endpoint only
- List: ✅ CrushingListScreen
- Form: ✅ CrushingFormScreen
- Details: ✅ CrushingDetailsScreen
- API: ❌ No show() endpoint

### MaterialIntakes — Missing: show() endpoint only
- List: ✅ MaterialIntakesListScreen
- Form: ✅ MaterialIntakeFormScreen
- Details: ✅ MaterialIntakeDetailsScreen
- API: ❌ No show() endpoint

---

*Last updated: 2026-08-03*
