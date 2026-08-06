# Mobile Receipt and Remittance Date Input Not Picker

## Issue

On the mobile Palletizing Receipt and Cash Remittance create/edit screens, the `Date` field is rendered as a plain text input instead of a date picker.

This allows invalid manual date entry and makes both forms harder to use on mobile.

## Current Behavior

- The date field is displayed as a text input with placeholder `YYYY-MM-DD`.
- The user must type the date manually.
- There is no native date picker interaction on either form.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/Receipt/ReceiptFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Remittance/RemittanceFormScreen.js`

## Expected Behavior

- The date field should use a proper date picker on both screens.
- The chosen value should still be stored and submitted in `YYYY-MM-DD` format.
- The field should validate as a date before saving.
- The mobile UX should match the rest of the app’s date-driven forms.

## Why This Matters

- Manual date typing can introduce invalid or inconsistent data.
- The user experience is slower than necessary.
- Other workflow forms in the app already use date picker patterns.

## Proposed Fix

- Replace the text input with a date picker component on both screens.
- Keep the formatted date string in state for submission.
- Add validation so invalid input cannot be saved.
- Preserve the current mobile theme and layout.

## Acceptance Criteria

- Receipt create/edit uses a date picker.
- Remittance create/edit uses a date picker.
- Both screens submit valid date strings only.
- No manual date typing is required for normal use.

