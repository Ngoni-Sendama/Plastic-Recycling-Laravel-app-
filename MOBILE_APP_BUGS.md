# Mobile App Bug Report — Highglen Ops

**Date:** 2026-08-06
**Reported by:** User (Admin Profile)
**Severity:** High

---

## BUG 1: Palletizing Production missing from mobile app — FIXED

### Summary
The "Palletizing Production" module does not exist in the mobile app. The Palletizing Site section in the drawer only shows "Receipt" — there is no way to view, create, or track palletizing production records (chips input → pellets output → loss).

### Impact
- Admin cannot see what was palletized at the palletizing site
- Admin cannot compare palletizing production output against crushing production input (chips received)
- The entire palletizing production workflow is invisible on mobile

### Root Cause
The backend is fully built (model, controller, routes, form schema, Filament admin, calculator service), but the mobile app UI was never created:
- No `src/screens/PalletizingProduction/` directory exists
- No imports or navigation stack in `App.js`
- No drawer menu item under "Palletizing Site"

### What exists (backend)
- `app/Models/PalletizingProduction.php` ✅
- `app/Http/Controllers/Api/PalletizingProductionController.php` ✅ (full CRUD)
- `app/Http/Resources/PalletizingProductionResource.php` ✅
- `app/Services/PalletizingProductionCalculator.php` ✅
- `routes/api.php` lines 88-92 ✅ (GET index, GET show, POST store, PATCH update, DELETE destroy)
- `FormSchemaController.php` module key `palletProd` ✅
- `src/sync.js` number prefix `PL-BATCH` ✅

### What was missing (mobile) — NOW FIXED
- `src/screens/PalletizingProduction/PalletizingProductionListScreen.js` ✅ Created
- `src/screens/PalletizingProduction/PalletizingProductionFormScreen.js` ✅ Created
- `src/screens/PalletizingProduction/PalletizingProductionDetailsScreen.js` ✅ Created
- App.js navigation stack and Drawer.Screen registration ✅ Added
- `CustomDrawerContent.js` drawer menu entry under "Palletizing Site" ✅ Added
- `routes/api.php` restore route ✅ Added (`POST /palletizing-productions/{id}/restore`)

### Data flow context
- **Palletizing Receipt** (exists on mobile) = what material arrives at palletizing site from dispatch
- **Palletizing Production** (now exists) = what was actually produced (chips → pellets), with input/output/loss tracking
- Admin can now verify yield and compare against crushing site output

---

## BUG 2: Admin cannot manage user roles from mobile app

### Summary
The Admin Profile on mobile cannot view or change user roles. The Users section is completely read-only — no edit button, no role picker, no create user.

### Impact
- Admin must use the web Filament panel to change any user's role
- Role assignments are invisible beyond a read-only badge on the details screen
- No way to onboard new users or modify permissions from the field

### Root Cause
Three-layer gap:

**1. Mobile UI is read-only:**
- `UsersScreen.js` — lists users with role filter, no edit/create actions
- `UserDetailsScreen.js` — shows user info + role badge, no edit button at all
- No `UserFormScreen.js` exists

**2. API has dual role system (out of sync):**
- `UserResource.php` returns only `role` (singular legacy column), NOT `roles` (Spatie relationship)
- `UpdateUserRequest.php` accepts only `role` as a single string, NOT a `roles` array
- Filament web form writes to Spatie `roles` pivot table, but API writes to legacy `role` column

**3. No role management endpoints:**
- No `GET /roles` endpoint to list available roles
- No `PATCH /users/{user}/roles` or equivalent
- `PATCH /users/{user}` exists but only handles legacy `role` column

### What exists (web admin)
- Filament form uses `CheckboxList::make('roles')->relationship('roles', 'name')` (multi-role via Spatie)
- Filament table shows `roles.name` badges
- Shield role resource at `/shield/roles` for full role CRUD
- Works correctly for assigning multiple roles per user

### What's missing (mobile)
- No `UserFormScreen.js` (edit/create)
- No role picker component
- `UserResource.php` doesn't return Spatie `roles` relationship
- `UpdateUserRequest.php` doesn't accept Spatie `roles` array
- No `GET /roles` API endpoint
- Mobile reads/writes legacy `role` column while Filament writes Spatie `roles` pivot

### Inconsistency detail
| Layer | Role field | Multiple roles? |
|---|---|---|
| Filament form (web) | Spatie `roles` relationship | Yes |
| Filament table (web) | `roles.name` | Yes |
| Filament infolist (web) | `role` (singular, legacy) | No |
| API resource | `role` (singular, legacy) | No |
| API update/store | `role` (singular, legacy) | No |
| Mobile app | `role` (singular, legacy) | No |

### Fix required
1. **API**: Update `UserResource` to return `roles` from Spatie. Update `UpdateUserRequest` to accept `roles` array. Add `GET /roles` endpoint.
2. **Mobile**: Create `UserDetailsScreen` edit button → `UserFormScreen` with role picker using roles from API.
3. **Migration**: Decide whether to keep legacy `role` column or fully migrate to Spatie `roles` and remove the old column.
