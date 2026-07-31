# Laravel API And Mobile App Sync

## Goal

Make the React Native mobile app communicate with the new Laravel API instead of the current Node.js backend.

The mobile app should keep the same user-facing workflows, but API routes, auth handling, and data shapes will need to be updated.

## Current Mobile API Usage

The current mobile app uses:

- `POST /api/auth/login`
- `GET /api/auth/me`
- `GET /api/users`
- `POST /api/users`
- `GET /api/records/:module`
- `POST /api/records/:module`
- `GET /api/dashboard`

Example payloads for each current module key are documented in
[`../example-data.md`](../example-data.md).

## Suggested Laravel API Routes

### Auth

- `POST /api/login`
- `POST /api/logout`
- `GET /api/user`

### Users

- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{user}`
- `PATCH /api/users/{user}`
- `DELETE /api/users/{user}`

### Materials

- `GET /api/materials`
- `POST /api/materials`
- `PATCH /api/materials/{material}`

### Workflow Records

- `GET /api/material-intakes`
- `POST /api/material-intakes`
- `GET /api/crushing-productions`
- `POST /api/crushing-productions`
- `GET /api/dispatches`
- `POST /api/dispatches`
- `GET /api/palletizing-receipts`
- `POST /api/palletizing-receipts`
- `GET /api/palletizing-productions`
- `POST /api/palletizing-productions`
- `GET /api/pellet-sales`
- `POST /api/pellet-sales`
- `GET /api/cash-remittances`
- `POST /api/cash-remittances`

### Dashboard

- `GET /api/dashboard`

### Sync

- `GET /api/sync/pull`
- `POST /api/sync/push`

## Mobile App Changes Needed

- Update `mobile/src/api.js` to point to the Laravel API.
- Update auth logic to use Laravel Sanctum token responses.
- Replace generic `/api/records/:module` calls with Laravel resource endpoints.
- Update each module config or add an API endpoint mapping per module.
- Add handling for validation errors returned by Laravel.
- Add local IDs for offline-created records.
- Add sync metadata fields to mobile records.

## Recommended API Response Shape

Use consistent JSON responses:

```json
{
  "data": {}
}
```

For lists:

```json
{
  "data": []
}
```

For validation errors:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## Implementation Notes

- Keep mobile field names predictable and map them clearly to Laravel column names.
- Consider Laravel API Resources for response formatting.
- Keep dashboard calculations server-side in Laravel for consistency.
- The mobile app can still calculate display previews, but saved records should trust backend-calculated values.
- Avoid changing every mobile screen at once; first create an API adapter layer that maps module keys to Laravel endpoints.
