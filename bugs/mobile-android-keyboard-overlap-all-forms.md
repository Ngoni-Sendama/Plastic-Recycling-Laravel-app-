# Mobile Android Keyboard Overlap On All Forms

## Issue

On Android, the on-screen keyboard overlaps inputs on multiple create/edit forms instead of pushing the content upward.

This affects the ability to complete forms because lower fields and the save button can be hidden behind the keyboard.

## Current Behavior

- Many mobile forms use a `ScrollView` with partial or inconsistent keyboard avoidance.
- Some screens wrap content in `KeyboardAvoidingView`, but the layout still does not scroll focused fields fully into view.
- Android users can lose access to lower inputs when typing.

## Affected Pattern

The issue is not limited to one screen. It affects the shared mobile form layout pattern used across workflow screens such as:

- Sales
- Dispatch
- Receipt
- Remittance
- Crushing
- Material Intake
- other create/edit screens with the same layout structure

## Likely Root Cause

- `KeyboardAvoidingView` is used inconsistently across forms.
- The `ScrollView` does not always have enough bottom padding or keyboard-aware behavior.
- Focused inputs are not being scrolled into view on Android.
- The Android window keyboard mode is not being handled strongly enough for all forms.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/*/*FormScreen.js`
- `Plastic-Recycling-Business-App/mobile/App.js`
- `Plastic-Recycling-Business-App/mobile/app.json`

## Expected Behavior

- The focused input should remain visible when the keyboard opens.
- Lower fields should not be hidden behind the keyboard.
- The save button should remain reachable.
- The behavior should be consistent across all forms.

## Proposed Fix

- Standardize a reusable keyboard-aware form wrapper.
- Add proper `keyboardShouldPersistTaps` and scroll behavior.
- Increase or compute bottom padding for Android.
- Ensure the native Android window mode supports resizing the content area.
- Keep existing theme and spacing unchanged.

## Acceptance Criteria

- Android keyboard no longer overlaps form inputs.
- Lower inputs and save buttons are visible while typing.
- The behavior is consistent across all create/edit screens.

## Resolution

**Partial:** All 10 form screens are intended to use a standardized keyboard avoidance pattern, but the fix still needs code-level verification and consistency checks:

```jsx
<KeyboardAvoidingView style={styles.container} behavior="padding" keyboardVerticalOffset={100}>
  <ScrollView contentContainerStyle={{ padding: 16, paddingBottom: 160 }} keyboardShouldPersistTaps="handled">
```

### Changes per screen

| Screen | Before | After |
|--------|--------|-------|
| `MaterialFormScreen.js` | No KAV at all | Added full KAV wrapping |
| `BuyerFormScreen.js` | No KAV at all | Added full KAV wrapping |
| `SalesFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `DispatchFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `ReceiptFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `ExpenseFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `CrushingFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `PalletizingProductionFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `RemittanceFormScreen.js` | `behavior` conditional on iOS only | `behavior="padding"` for both platforms |
| `MaterialIntakeFormScreen.js` | Already correct | Updated `paddingBottom` to 160 for consistency |

### Key changes
- **`behavior="padding"`** works reliably on both iOS and Android (not just iOS)
- **`keyboardVerticalOffset={100}`** accounts for the navigation header height
- **`keyboardShouldPersistTaps="handled"`** prevents keyboard dismiss when tapping buttons
- **`paddingBottom: 160`** ensures the save button is always reachable above the keyboard
- Removed unused `Platform` imports from 4 screens
