# Mobile Dispatch Form Keyboard Overlap

## Issue

On the mobile Dispatch create/edit screen, the on-screen keyboard overlaps some inputs.

This makes it hard to reach lower fields and interferes with form completion.

## Current Behavior

- The form uses a scroll view with keyboard avoidance.
- On Android, the keyboard can still cover lower fields.
- Some inputs may become partially hidden when typing.

## Relevant File

- `Plastic-Recycling-Business-App/mobile/src/screens/Dispatch/DispatchFormScreen.js`

## Expected Behavior

- The focused field remains visible when the keyboard opens.
- Lower inputs should scroll into view.
- Save and back actions should remain accessible.

## Proposed Fix

- Improve keyboard-aware scrolling on the Dispatch form.
- Increase bottom padding or use a keyboard-aware container.
- Keep the existing theme and spacing style.

## Acceptance Criteria

- The Dispatch form can be fully used on Android with the keyboard open.
- No important input is hidden behind the keyboard.
- The user can save without closing the keyboard first.

## Resolution

**Fixed:** Added keyboard-aware auto-scrolling and increased bottom padding.

- Added `Keyboard` listener that triggers scroll when keyboard appears
- Added `ref` + `onFocus`/`onBlur` tracking on all 3 TextInput fields (Weight, Batch ref, Transported by)
- `scrollToInput()` uses `measureLayout` to scroll the focused field 120px from the top
- Increased `paddingBottom` from 160 to 300 to ensure save button is always reachable
- Added `scrollRef` to ScrollView for programmatic scrolling

