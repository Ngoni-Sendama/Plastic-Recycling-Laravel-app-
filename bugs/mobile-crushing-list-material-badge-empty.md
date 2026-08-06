# Mobile Crushing List Material Badge Empty

## Issue

On the Crushing Production list page, the material badge is empty.

Instead of showing the material code or a meaningful label, the badge renders blank.

## Current Behavior

- The list card shows a badge area for the material.
- The badge has no visible text, or the material code is missing.
- The user cannot quickly identify the material for each crushing record.

The same issue also appears on:

- the Crushing edit screen
- the Crushing print output

## Relevant File

- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingListScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingDetailsScreen.js`

## Likely Root Causes

- The API payload may not include the material relation or material code in the shape the UI expects.
- The list screen may be reading the wrong field, such as `material`, `material_code`, or a nested relation that is not loaded.
- The offline sync mapping may be storing the record correctly, but the UI renderer is not using the correct display field.

## Expected Behavior

- The badge should show the material code, or a fallback like the material name.
- If material data is missing, the UI should show a safe fallback such as `Unknown`.
- The badge should never render as empty when a material exists.
- The edit screen should prefill the selected material correctly.
- The print output should include the material code or name.

## Proposed Fix

- Verify the mobile list view field mapping.
- Verify the API/resource payload for crushing productions includes a readable material value.
- Add a fallback chain in the list renderer so the badge always has text.
- Confirm offline cached records use the same display field as online ones.
- Ensure the edit form reads the same field the create form saves.
- Ensure the print template uses the same fallback chain as the list and details screens.

## Acceptance Criteria

- Crushing list cards show a visible material code or label.
- No empty badge appears when material data exists.
- The behavior is consistent online and offline.
- Edit screens show the selected material correctly.
- Print output shows the material consistently.
