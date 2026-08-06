# Mobile Sales Bluetooth Button Closes App

## Issue

On the Pellet Sales details screen, tapping the Bluetooth print button closes the app or shows a system-level crash message.

Instead of failing gracefully, the app exits or becomes unstable when Bluetooth printing is attempted.

## Current Behavior

- The Sales details screen has a Bluetooth print button.
- Tapping it triggers the native thermal printer flow.
- In the current runtime/build combination, the app can close unexpectedly.

## Likely Root Cause

- The Bluetooth printer module is a native dependency.
- If the app is running in Expo Go or a build without the native module properly registered, the module can crash the runtime.
- The printer helper currently tries to load and use the native module at print time.
- If the printer call fails hard enough, the app may terminate instead of showing a handled error.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/screens/Sales/SalesDetailsScreen.js`
- `Plastic-Recycling-Business-App/mobile/src/services/thermalPrinter.js`
- `Plastic-Recycling-Business-App/mobile/package.json`
- `Plastic-Recycling-Business-App/mobile/eas.json`

## Expected Behavior

- Tapping Bluetooth print should never close the app.
- If the native module is unavailable, the app should show a clear message instead.
- The app should distinguish between:
  - Expo Go / unsupported build
  - no paired printer
  - printer connection failure
  - successful print

## Proposed Fix

- Guard the Bluetooth button so it is only enabled in a dev/native build that includes the printer module.
- Add a runtime capability check before attempting to connect to the printer.
- Wrap all printer initialization and printing calls in safe error handling.
- Show a user-friendly alert when Bluetooth printing is not available.
- Keep the PDF print button as a fallback.

## Acceptance Criteria

- Bluetooth print does not crash or close the app.
- Unsupported runtimes show a clear error message.
- Native/dev builds still allow Bluetooth printing when a printer is paired.
- The Sales details screen remains stable even if printing fails.

