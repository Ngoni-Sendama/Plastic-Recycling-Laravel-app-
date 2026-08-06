# Mobile Crushing Form Keyboard Overlap

## Issue

On the Crushing Production create/edit screen, the on-screen keyboard overlaps lower form fields such as:

- Output Chips (kg)
- GRN Reference
- the save button area

This makes the bottom part of the form difficult or impossible to use on Android devices.

## Current Behavior

- The screen already uses `KeyboardAvoidingView`.
- The scroll area is a plain `ScrollView` without stronger keyboard-aware handling.
- When the keyboard opens, lower inputs can be hidden behind it.

## Relevant File

- `Plastic-Recycling-Business-App/mobile/src/screens/Crushing/CrushingFormScreen.js`

## Why This Happens

- `KeyboardAvoidingView` alone is not always enough on Android.
- The `ScrollView` does not guarantee that focused inputs scroll into view.
- The current layout lacks a dedicated keyboard-aware wrapper or stronger bottom padding strategy.

## Expected Behavior

- When the keyboard opens, the focused field stays visible.
- The user can reach:
  - Output Chips (kg)
  - GRN Reference
  - Save button
- The form should be usable on small Android screens without manual screen rotation or excessive scrolling.

## Proposed Fix

- Use a keyboard-aware scroll container or improve the current `ScrollView` behavior.
- Add better bottom padding / keyboard offset on Android.
- Ensure the focused input scrolls into view.
- Keep the existing visual theme unchanged.

## Acceptance Criteria

- The Crushing form is usable with the keyboard open on Android.
- No lower input is hidden by the keyboard.
- Save and back actions remain accessible.

## Resolution

**Fixed:** Added keyboard-aware auto-scrolling and increased bottom padding.

### Changes
- Added `Keyboard` listener that triggers scroll when keyboard appears
- Added `ref` + `onFocus`/`onBlur` tracking on all 3 TextInput fields (Input Weight, Output Chips, GRN Reference)
- `scrollToInput()` uses `measureLayout` to scroll the focused field 120px from the top
- Increased `paddingBottom` from 160 to 300 to ensure save button is always reachable
- Added `scrollRef` to ScrollView for programmatic scrolling

