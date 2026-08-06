# Mobile Hide Restricted Navigation

## Issue

The app currently shows a permission-denied error message like:

`You do not have permission to perform this action.`

Instead of showing that message, the mobile app should hide navigation items that the current user cannot access.

## Current Behavior

- The mobile drawer already hides `Manage users` for non-admin users.
- Some restricted screens or actions still reach the API and return HTTP 403.
- The backend middleware `EnsureApiPermission` returns the permission-denied JSON message.
- The user sees an error after tapping a route/action they should not even see.

## Root Cause

There are two layers involved:

1. Mobile navigation visibility
   - Some items are hidden in the drawer.
   - Other restricted routes or actions may still be reachable through navigation, deep links, or button actions.

2. Backend authorization
   - The API correctly blocks unauthorized access.
   - The user sees the backend 403 message because the UI is still allowing the action to be attempted.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/navigation/CustomDrawerContent.js`
- `Plastic-Recycling-Business-App/mobile/App.js`
- `app/Http/Middleware/EnsureApiPermission.php`
- `app/Policies/*`
- `app/Filament/*` if a web permission issue is also affected

## Expected Behavior

- If a user does not have access, the navigation item should not be shown.
- The user should not be able to tap into a screen they cannot access.
- Restricted routes should be removed from any mobile menu, drawer, or action list.
- The backend should still keep authorization checks as a safety net, but the user should not normally see the 403 message.

## Proposed Fix

- Add a centralized permission map for mobile navigation.
- Hide drawer entries based on role/permission checks before rendering.
- Guard any nested screens or action buttons that can still be opened directly.
- Keep backend permission middleware as the final enforcement layer.
- Replace backend 403 user-facing messaging with silent hiding wherever possible.

## Acceptance Criteria

- Non-authorized users do not see restricted navigation items.
- Tapping the drawer no longer leads to a permission error for hidden features.
- Admin-only screens stay visible only to admin users.
- Backend authorization still prevents direct unauthorized API calls.

## Notes

- The current drawer already hides `Manage users` via `adminOnly`.
- This bug is broader than that single item: all restricted mobile navigation should be hidden by role/permission.
- If the app later adds more roles, the permission map should be expanded in one place rather than scattered across screens.

