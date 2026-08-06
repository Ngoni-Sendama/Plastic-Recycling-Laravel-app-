# Mobile Sales Date Input Not Picker

## Issue

On the mobile Pellet Sales create/edit screen, the `Date` field is rendered as a plain text input instead of a date picker.

This allows manual date typing and makes the form less consistent with the rest of the workflow screens.

## Current Behavior

- The date field is shown as a text input with placeholder `YYYY-MM-DD`.
- The user must type the date manually.
- There is no native date picker on the Sales form.

## Relevant File

- `Plastic-Recycling-Business-App/mobile/src/screens/Sales/SalesFormScreen.js`

## Expected Behavior

- The date field should use a proper date picker.
- The selected value should still be stored and submitted in `YYYY-MM-DD` format.
- The field should validate as a date before saving.
- The mobile UX should be consistent with other workflow forms.

## Why This Matters

- Manual typing can introduce invalid or inconsistent dates.
- The user experience is slower than necessary.
- Other records in the app should follow the same date-entry pattern.

## Proposed Fix

- Replace the text input with a date picker component.
- Keep the formatted date string in state for submission.
- Add validation so invalid input cannot be saved.
- Preserve the current theme and layout.

## Acceptance Criteria

- Sales create/edit uses a date picker.
- Sales save valid date strings only.
- No manual date typing is required for the date field.
