# Mobile Dispatch Date Input Not Picker

## Issue

On the mobile Dispatch create/edit screen, the `Date` field is rendered as a plain text input instead of a date picker.

This allows invalid manual entry and makes the form less consistent with the rest of the app.

## Current Behavior

- The date field is shown as a text box with placeholder `YYYY-MM-DD`.
- The user must type the date manually.
- There is no picker UI on the mobile form.

## Relevant File

- `Plastic-Recycling-Business-App/mobile/src/screens/Dispatch/DispatchFormScreen.js`

## Expected Behavior

- The date field should use a proper date picker.
- The value should still save in `YYYY-MM-DD` format.
- The field should validate as a date before submit.
- The UI should match the rest of the app’s form behavior.

## Why This Matters

- Manual typing can introduce invalid dates.
- The mobile app already uses date pickers in other forms.
- Dispatch is a workflow record and should be quick and consistent to enter.

## Proposed Fix

- Replace the text input with a date picker component.
- Keep the formatted date in state for submission.
- Add validation so an invalid value cannot be saved.
- Preserve the current theme styling.

## Acceptance Criteria

- Dispatch create/edit shows a date picker.
- The submitted payload contains a valid date string.
- The user cannot accidentally enter an invalid date format.

