# Mobile Palletizing Receipt Edit Existing Record Not Loaded

## Issue

On the mobile Palletizing Receipt edit flow, the selected record is not being loaded into the form.

The edit screen opens with empty or default values instead of the existing receipt data.

## Current Behavior

- The Receipt form reads `route.params?.editRecord` as the existing record.
- The list/details flow may pass a `record` param instead.
- Because of that mismatch, the form can treat edit as create.
- Existing values such as date, GRN number, dispatch reference, material, weight, and rate are not prefilled.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/Receipt/ReceiptFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Receipt/ReceiptListScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Receipt/ReceiptDetailsScreen.js`

## Likely Root Cause

- Route param mismatch:
  - form expects `editRecord`
  - navigation may be passing `record`
- The form’s initial state depends on the existing record object.
- When the object is missing, the screen opens with blank/default fields.

## Expected Behavior

- Opening Receipt edit should preload:
  - date
  - GRN number
  - dispatch reference
  - material
  - weight received
  - rate per kg
- The selected record should be visible and editable immediately.

## Proposed Fix

- Standardize the route param name across Receipt navigation paths.
- Make the form accept both `record` and `editRecord`, or update all navigation calls to use one name consistently.
- Confirm the selected material and date are mapped correctly from the saved payload.
- Add a regression check for receipt edit prefill behavior.

## Acceptance Criteria

- Opening Receipt edit from list/details preloads the selected record.
- No blank edit form appears for an existing receipt.
- The user can change and save the existing values without re-entering the whole record.
