# Laravel Sanctum Authentication

## Goal

Use Laravel Sanctum for API authentication between the mobile app and the Laravel backend.

For a mobile app, token-based Sanctum auth is the simplest approach.

## Recommended Auth Flow

1. User enters username and password in the mobile app.
2. Mobile app sends credentials to `POST /api/login`.
3. Laravel validates credentials.
4. Laravel creates a Sanctum personal access token.
5. Mobile app stores the token securely.
6. Mobile app sends the token on API requests:

```http
Authorization: Bearer <token>
```

7. Logout deletes the current token.

## Suggested Laravel Routes

```php
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

## Login Request

```json
{
  "username": "admin",
  "password": "admin123"
}
```

## Login Response

```json
{
  "token": "plain-text-token",
  "user": {
    "id": 1,
    "name": "System admin",
    "username": "admin",
    "role": "Admin"
  }
}
```

## Mobile Storage

The app currently stores the token in AsyncStorage. This works for testing, but for production consider secure storage.

Recommended production option:

- `expo-secure-store`

## User Model Requirements

The Laravel `User` model should use:

```php
use Laravel\Sanctum\HasApiTokens;
```

The model should support login by username. Laravel defaults to email, so the auth controller must query by `username`.

## Role Handling

The token identifies the user, but authorization should still be enforced in Laravel.

Recommended access control:

- Admin can manage users and all records.
- Stock controller can manage intake and stock-related records.
- Crusher operator can manage crushing records.
- Stock receiver can manage palletizing receipts.
- Palletizing operator can manage palletizing production.
- Supervisor can view dashboard and reports.

## Implementation Notes

- Do not return password hashes.
- Do not expose all user columns to the mobile app.
- Add logout support in the mobile app.
- Add token refresh strategy only if needed. Sanctum personal access tokens can be long-lived, but token expiry policy should be decided before production.
- Rate-limit login attempts.
