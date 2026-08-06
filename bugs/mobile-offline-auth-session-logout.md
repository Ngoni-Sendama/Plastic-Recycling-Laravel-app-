# Mobile Offline Auth Session Logout

## Issue

When the app is closed and reopened, it logs the user out if the device is offline.

This breaks the offline-first requirement because a user who already signed in should remain signed in locally, even without internet. The app should only refresh the session from the server when connectivity is available.

## Current Behavior

- Login succeeds only when the server is reachable.
- Session is stored locally in `AsyncStorage`.
- On app start, the app loads the saved session.
- The app immediately calls `GET /user` through `apiGetUser(session.token)`.
- If that request fails for any reason, the app clears the local session and logs the user out.

## Root Cause

The restore flow in `Plastic-Recycling-Business-App/mobile/src/context/AuthContext.js` treats any failure from `apiGetUser()` as a hard session failure.

That means:

- no network
- slow server
- temporary DNS failure
- timeout
- API downtime

all produce the same result: the saved session is deleted.

## Relevant Files

- `Plastic-Recycling-Business-App/mobile/src/context/AuthContext.js`
- `Plastic-Recycling-Business-App/mobile/src/api.js`
- `Plastic-Recycling-Business-App/mobile/src/storage.js`

## Why This Is Wrong

- The app already has local storage for the session.
- Offline-first behavior should trust the locally saved token until the server can be reached again.
- Logging out on network failure makes the app unusable offline.

## Expected Behavior

- If a saved session exists, restore it locally first.
- Keep the user signed in offline.
- If the network is available, refresh the token/session from the server in the background.
- If the server returns `401`, then clear the session and show the expired-session message.
- If the server is unreachable, do not clear the session.

## Proposed Fix

- Change `restore()` so it does not clear the session on any request failure.
- Distinguish between:
  - `401 Unauthorized` -> session expired, sign out
  - network/timeout/offline -> keep local session, show offline warning if needed
- Add a local auth flag so the app can resume from cached session without blocking on the server.
- Optionally queue a token refresh check for the next time the device is online.

## Acceptance Criteria

- Closing and reopening the app while offline keeps the user signed in.
- Existing local session is used without hitting the server first.
- The app only logs out when the server explicitly says the session is invalid.
- When connectivity returns, the app revalidates the session and refreshes auth state.

## Notes

- This is a blocker for full offline support.
- The current behavior is technically a “server-verified session” flow, not offline auth.
- Once this is fixed, the rest of the offline-first sync model can behave consistently.

