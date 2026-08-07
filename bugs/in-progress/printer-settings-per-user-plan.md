# Printer settings per authenticated user

## Problem

The current QZ Tray printing flow on the web works only when the printer name is known in advance. That is fragile because:

- printer names can change when the PC is replaced or renamed
- different cashiers may use different Windows PCs
- one user may have a dedicated printer while another user uses a different one
- a hardcoded printer name like `POS58 Printer` is not safe for production

## Goal

Build a printer settings page that lets each authenticated user save their own printer assignment for QZ Tray printing.

## Expected Behavior

- A logged-in user can open a printer settings page.
- The page connects to QZ Tray and lists available printers.
- The user selects the printer they want to use.
- The selection is saved against the authenticated user.
- When the same user prints Material Intake or any future QZ-enabled document, the app uses their saved printer automatically.
- If the printer name changes or the printer is missing, the user can reassign it without code changes.

## Scope

### In scope

- printer discovery from QZ Tray
- user-specific printer assignment
- local fallback if the saved printer is missing
- test print for the selected printer
- per-user persistence using the authenticated `user_id`

### Out of scope for now

- company-wide printer sharing
- multi-machine sync of the same printer choice
- role-based printer defaults
- printer groups for multiple document types

## Proposed Data Model

Store printer settings per authenticated user.

Suggested fields:

- `user_id`
- `printer_name`
- `printer_type` or `device_label` if needed later
- `paper_size` or `receipt_width`
- `is_default`
- `last_tested_at`
- `created_at`
- `updated_at`

## Proposed Flow

1. User opens Printer Settings.
2. App asks QZ Tray for the list of available printers.
3. App displays the printer list.
4. User selects the correct printer.
5. App saves the printer mapping to the current authenticated user.
6. When printing a receipt, the system loads the saved printer for that user.
7. If the printer is missing, the UI prompts the user to reselect one.

## Important Design Rules

- Never hardcode printer names in the print action.
- Always use the saved printer name for the current authenticated user.
- Keep a manual reselect option if the printer is renamed or disconnected.
- Keep the print flow working even if the browser is on a different PC for a different user.
- Do not store one global printer for all users unless a shared-printer mode is added later.

## Recommended Implementation Order

1. Create a printer settings page.
2. Create a printer settings record linked to `user_id`.
3. Add a printer discovery button that calls `qz.printers.find()`.
4. Add a save action for the selected printer.
5. Add a test print action.
6. Update Material Intake printing to read the current user's saved printer.
7. Add fallback logic when the printer is missing.
8. Extend the same pattern to other QZ-print pages later.

## Notes

- The authenticated user should own their printer settings.
- This fits cashier workflows better because different users may work on different PCs or printers.
- If a user logs into another PC, they should still be able to choose a printer on that machine and save it to their account.

