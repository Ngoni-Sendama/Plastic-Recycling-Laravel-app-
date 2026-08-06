# Mobile Material Intake Create Not Saving

## Issue

The Material Intake create flow on mobile is not saving a new record correctly.

The user can fill in the form, but after submit the record does not appear to persist as expected.

## Current Behavior

- The form collects:
  - date
  - buyer
  - material
  - gross weight
  - tare weight
  - unit price
- The submit action calls the offline sync create path:
  - `createRecord("material_intakes", payload, user?.token, user?.fullName)`
- After save, the record is not showing as a successful saved intake in the expected way.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/MaterialIntakes/MaterialIntakeFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/sync.js`
- `app/Http/Controllers/Api/MaterialIntakeController.php`
- `app/Http/Requests/Api/StoreMaterialIntakeRequest.php`
- `app/Models/MaterialIntake.php`
- `app/Http/Resources/MaterialIntakeResource.php`

## Likely Root Causes

- The mobile create payload may be missing a field the sync/API layer expects.
- The sync engine may be failing during immediate push after local save.
- The backend may still require a denormalized field or relation value that is not being passed in the shape expected by the API resource/request.
- The local record may be created as pending but not syncing successfully, making it look like the save failed.

## Notes From Diagnostics

- The mobile form uses `material_code` and `buyer_id`.
- The backend controller resolves `buyer_name` from `buyer_id`.
- The form uses offline-first `createRecord(...)`, so a failure can happen either:
  - during local save
  - during immediate sync push
  - during backend validation or model insert

## Expected Behavior

- Submitting the form should create a local pending Material Intake record immediately.
- The record should sync successfully when the server is reachable.
- The saved record should appear in the list and details screens with the expected buyer/material display values.

## Proposed Fix

- Verify the exact payload passed to `createRecord`.
- Verify the form-schema mapping for `material_intakes`.
- Confirm the immediate sync push is not failing on validation or missing denormalized fields.
- Confirm the backend accepts the mobile payload shape and stores the record without requiring extra manual fields.
- Add clearer user-facing error handling when create fails.

## Acceptance Criteria

- Creating a Material Intake on mobile stores the record locally.
- The record syncs successfully when online.
- The user sees a success message and the new record appears in the list.
- Failures show a clear validation or sync error instead of silently not saving.
