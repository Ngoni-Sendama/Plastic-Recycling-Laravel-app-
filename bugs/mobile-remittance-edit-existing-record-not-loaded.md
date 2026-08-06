# Mobile Remittance Edit Existing Record Not Loaded

## Issue

On the mobile Cash Remittance edit flow, the selected record is not being loaded into the form.

The edit screen opens with empty or default values instead of the existing remittance data.

## Current Behavior

- The Remittance form reads `route.params?.editRecord` as the existing record.
- The list/details flow passes `record` to the details screen, and edit navigation may pass the same shape.
- Because of that mismatch, the form can open as if it were creating a new record.
- Existing values such as date, voucher number, period covered, chips delivered, recovery price, sales revenue, and cash remitted are not prefilled.
- The `recorded_by` display also falls back to `-` when the payload does not include the expected field or relation.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/Remittance/RemittanceFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Remittance/RemittanceListScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Remittance/RemittanceDetailsScreen.js`
- `app/Http/Resources/CashRemittanceResource.php`
- `app/Http/Controllers/Api/CashRemittanceController.php`
- `app/Models/CashRemittance.php`

## Likely Root Cause

- Route param mismatch:
  - form expects `editRecord`
  - navigation may be passing `record`
- The form’s initial state depends on the existing record object.
- When the object is missing, the screen opens with blank/default fields.
- The recorded-by dash likely comes from the API/resource not including or not resolving the related user value in the shape the UI expects.

## Expected Behavior

- Opening Remittance edit should preload:
  - date
  - voucher number
  - period covered
  - chips delivered
  - recovery price
  - sales revenue
  - cash remitted
- The recorded-by value should display the user name where available instead of `-`.
- The user should be able to edit the existing remittance record without re-entering everything.

## Proposed Fix

- Standardize the route param name across Remittance navigation paths.
- Make the form accept both `record` and `editRecord`, or update navigation to use one key consistently.
- Confirm the API resource always returns `recorded_by` when the relation exists.
- Ensure the list/details/edit screens use the same payload shape for remittance records.
- Add a regression check for remittance edit prefill and recorded-by display.

## Acceptance Criteria

- Opening Remittance edit from list/details preloads the selected record.
- No blank edit form appears for an existing remittance.
- The recorded-by label shows the user name when the data exists.
- The user can save changes without re-entering the record from scratch.

