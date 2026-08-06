# Mobile Crushing Material Not Showing

## Issue

On the Crushing Production create/edit flow, the selected material is not showing correctly after save.

Instead of the chosen material, the UI still displays a dash or `–` symbol in the list/details view.

## Current Behavior

- The Crushing form lets the user select a material.
- The form submits `material_id`.
- After save, the record display still shows `–` instead of the material code/name.

## Likely Root Causes

- The saved record may not be loading the related `material` model when rendering the list/details screen.
- The mobile display logic may be reading `record.material` or `record.material_code`, but the returned payload may not contain either field.
- The API resource for crushing productions may not be mapping the material relation into the response consistently.
- The local sync mapping may not preserve the material relation payload correctly.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingFormScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingListScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingDetailsScreen.js`
- `app/Http/Controllers/Api/CrushingProductionController.php`
- `app/Http/Resources/CrushingProductionResource.php`
- `app/Models/CrushingProduction.php`
- `app/Filament/Resources/CrushingProductions/*`

## Expected Behavior

- The selected material should save correctly.
- The list screen should show the material code or name instead of `–`.
- The details screen should show the linked material consistently.
- Offline sync should preserve the relationship value and render it after restore.

## Proposed Fix

- Verify that the API response includes the material relation or a readable material field.
- Confirm that the resource maps the relation to a display value.
- Confirm that the mobile sync mapping stores the field the UI expects.
- Update the Crushing list/details screen to use the correct field from the API payload.

## Acceptance Criteria

- Creating or editing a Crushing Production record shows the selected material after save.
- No dash placeholder appears when a material exists.
- The same result works both online and offline after sync.
