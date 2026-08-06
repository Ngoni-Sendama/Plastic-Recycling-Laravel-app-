# Mobile Dispatch Edit Existing Record Not Loaded

## Issue

On the mobile Dispatch edit flow, the selected record is not being loaded into the form.

The edit screen opens with empty or default values instead of the existing dispatch data.

## Current Behavior

- The Dispatch form reads `route.params?.editRecord` as the existing record.
- Other screens in the app commonly navigate using a `record` param.
- Because of this mismatch, the form often treats edit as create.
- Existing values such as date, material, weight, batch reference, and transported by are not prefilled.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/Dispatch/DispatchFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Dispatch/DispatchListScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Dispatch/DispatchDetailsScreen.js`
- `Plastic-Recycling-Business-App/mobile/App.js`

## Likely Root Cause

- Route param name mismatch:
  - form expects `editRecord`
  - navigation may be passing `record`
- The form’s initial state depends on the existing record object.
- When the object is missing, all fields fall back to empty/default state.

## Expected Behavior

- Editing a selected dispatch record should preload:
  - date
  - material
  - weight dispatched
  - batch reference
  - transported by
- The screen should clearly open in edit mode with the current values visible.
- The same selected record should be used consistently across list, details, and edit screens.

## Proposed Fix

- Standardize the route param name across all dispatch navigation paths.
- Ensure the edit button passes the same key that the form expects, or update the form to accept both `record` and `editRecord`.
- Confirm the material field and date field are mapped from the saved payload.
- Add a regression check for dispatch edit prefill behavior.

## Acceptance Criteria

- Opening Dispatch edit from list/details preloads the selected record.
- No empty form appears when editing an existing record.
- The user can change and save the existing dispatch values without re-entering everything.
