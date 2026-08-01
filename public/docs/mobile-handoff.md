# Plastic Recycling — Mobile API Handoff

Everything the React Native team needs to start integrating with the backend API.

---

## 1. Base URL

| Environment | Base URL |
|---|---|
| **Dev (local / Herd)** | `https://plastic-recycling.test/api` |
| **Staging / Production** | *Set by the deployment team — see §7 Deployment checklist* |

> ⚠️ `plastic-recycling.test` is a local Herd domain. For on-device testing, point the app at a deployed server and swap the base URL — no code changes needed.

All endpoints below are relative to the base URL, e.g. `POST https://plastic-recycling.test/api/login`.

---

## 2. Auth flow (Sanctum tokens)

1. **Login** — `POST /login`

   ```json
   // Request
   { "username": "crusher01", "password": "password123" }

   // 200 OK
   {
     "token": "1|abc123...",
     "user": { "id": 2, "name": "Tawanda Moyo", "username": "crusher01", "role": "Crusher operator" }
   }
   ```

2. **Store the token** and send it on every request:

   ```
   Authorization: Bearer 1|abc123...
   ```

3. **Get the current user** — `GET /user` (returns the `user` object). Use the `role` field to gate screens by role.
4. **Logout** — `POST /logout` (revokes the current token; returns `{ "message": "Logged out successfully." }`).

### Error conventions

| Status | Meaning | Client action |
|---|---|---|
| `401` | Missing/invalid/expired token | Re-login |
| `403` | Authenticated, but role lacks permission | Hide/disable the feature — **do not retry** |
| `422` | Validation failed (`errors` map in body) | Show field errors |
| `404` | Route/resource not found | Show not-found state |
| `429` | Rate limited (login: 5 attempts/min) | Back off and retry |

---

## 3. Test accounts

All seeded via `php artisan db:seed`. Passwords from `UserSeeder`.

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | super_admin + Admin |
| `stock01` | `password123` | Stock controller |
| `crusher01` | `password123` | Crusher operator |
| `receiver01` | `password123` | Stock receiver |
| `palletizing01` | `password123` | Palletizing operator |
| `supervisor01` | `password123` | Supervisor |

Each account's token only works for endpoints its role is allowed to call (Shield RBAC enforced server-side). Use the `admin` account to test everything.

---

## 4. Endpoint map (32 endpoints)

### Auth
| Method | Path | Auth |
|---|---|---|
| POST | `/login` | Public (rate-limited) |
| GET | `/user` | 🔒 |
| POST | `/logout` | 🔒 |

### Users & Materials (reference data)
| Method | Path | Permission |
|---|---|---|
| GET | `/users?search=<term>` | Admin |
| POST | `/users` | Admin |
| GET | `/users/{user}` | Admin |
| PATCH | `/users/{user}` | Admin |
| DELETE | `/users/{user}` | Admin |
| GET | `/materials` | All roles |
| POST | `/materials` | Admin |
| PATCH | `/materials/{material}` | Admin |

### Workflow modules (list + create per module)
| Module | Path | Who can create |
|---|---|---|
| Material intakes | `/material-intakes` | Stock controller |
| Crushing productions | `/crushing-productions` | Crusher operator |
| Dispatches | `/dispatches` | Crusher operator / Stock controller |
| Palletizing receipts | `/palletizing-receipts` | Stock receiver |
| Palletizing productions | `/palletizing-productions` | Palletizing operator |
| Pellet sales | `/pellet-sales` | Admin |
| Cash remittances | `/cash-remittances` | Admin |

> All are `GET /<module>` (list) and `POST /<module>` (create). POST is restricted per the table. GET requires the module's view permission — only **Admin, Stock controller, and Supervisor** can list *every* module; other roles can list only their own modules (a `403` means the role can't view that module, so hide it in the app). Offline sync is auth-only for **all** roles.

### Dashboard & Reports (read-only)
| Method | Path | Access |
|---|---|---|
| GET | `/dashboard` | All roles |
| GET | `/reports/stock` | Admin, Stock controller, Supervisor |
| GET | `/reports/production` | Admin, Supervisor |
| GET | `/reports/sales` | Admin, Supervisor |
| GET | `/reports/cash-reconciliation` | Admin, Stock controller, Supervisor |

> Reports are **not** available to Crusher operator, Stock receiver, or Palletizing operator (they'll get `403`). Stock controller can only access stock + cash-reconciliation — build report screens only for the roles listed above.

### Offline sync (doc 05)
| Method | Path | Purpose |
|---|---|---|
| GET | `/sync/pull?since=<ISO-8601>` | Pull changes since timestamp |
| POST | `/sync/push` | Push local changes (rows include `lock_version` for conflict detection) |

---

## 5. Reference materials

| Resource | Location |
|---|---|
| Hosted Swagger UI (browse in browser) | `https://plastic-recycling.test/docs` |
| OpenAPI spec (machine-readable) | `public/docs/openapi.yaml` |
| Full API reference (RBAC matrix, sync flow, curl checks) | `public/docs/api-reference.md` |
| Postman collection (all 32 endpoints, token auto-capture) | `public/docs/plastic-recycling.postman_collection.json` |

**Recommended workflow:** import the Postman collection → log in with a test account → the collection auto-stores the token for all authenticated calls.

---

## 6. Mobile integration checklist

- [ ] Swap base URL per environment (dev / staging / prod)
- [ ] Store token securely (Keychain/Keystore) after login
- [ ] Handle `401` → prompt re-login; `403` → hide/disable screen
- [ ] Use the `role` from login response to gate navigation
- [ ] Use `/sync/pull` + `/sync/push` for offline-first operation
- [ ] Register devices/users via `POST /users` (Admin only)

---

## 7. Deployment checklist (hosting for real device testing)

For on-device testing the phone must reach a **public HTTPS server** — `plastic-recycling.test` is local-only. Deploy to any PHP host (Laravel Cloud, VPS, shared hosting) with the steps below.

### 7.1 Server requirements

- PHP **8.4+** with required Laravel extensions (SQLite or PDO for your DB driver)
- Composer, Node + npm (for building the Filament panel assets)
- Web server pointed at the **`public/`** directory (never the project root)
- **HTTPS** on the domain — mobile devices should never talk to a plain-HTTP API in production

### 7.2 Required env vars (`.env` on the server)

| Key | Value | Why it matters |
|---|---|---|
| `APP_ENV` | `production` | Disables dev-only behaviors |
| `APP_DEBUG` | `false` | Never leak stack traces to the API |
| `APP_URL` | `https://your-domain.com` | Must match what the phone hits; drives storage URLs + Sanctum links (**no trailing slash**) |
| `APP_KEY` | generate via `php artisan key:generate` | Required — app will not boot without it |
| `DB_CONNECTION` | `sqlite` (file, fine for a pilot) or `mysql`/`pgsql` | Change to a real DB for multi-device production; then set `DB_HOST`/`DB_PORT`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` |
| `FILESYSTEM_DISK` | `public` | Makes uploaded files (avatars) web-accessible |
| `FILAMENT_EDIT_PROFILE_DISK` | `public` | Avatar uploads (default already `public`) |
| `SANCTUM_STATEFUL_DOMAINS` | your domain (optional for mobile) | Only needed for **SPA cookie** auth; the mobile app uses bearer tokens, so the default is fine |
| `SESSION_DRIVER` | `database` | Ships as `database`; sessions table exists via migration |
| `QUEUE_CONNECTION` | `database` | Job queue; run a worker if you add queued work |
| `CACHE_STORE` | `database` | Default already `database` |
| `LOG_CHANNEL` | `stack` | Keep for unified logs |

> **Security:** `php artisan db:seed --force` creates **6 staff accounts with known default passwords** (`admin`/`admin123`, everyone else `password123` — see §3). **Rotate every password immediately after seeding** — these are published defaults, not real credentials.

### 7.3 Deploy steps (in order)

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # builds the Filament/admin panel assets
php artisan key:generate         # if APP_KEY is empty
php artisan migrate --force      # create all tables
php artisan db:seed --force      # roles + permissions + staff accounts (see §3)
php artisan storage:link         # symlink so avatars resolve at /storage/...
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:work           # start the job worker (QUEUE_CONNECTION=database); supervise with systemd/Supervisor
```

### 7.4 Post-deploy smoke test

Run this **before rotating the seeded passwords** (or substitute the new credentials):

```bash
curl -s https://your-domain.com/up          # → "ok" (Laravel health check)
curl -s https://your-domain.com/api/login -X POST -H 'Content-Type: application/json' -d '{"username":"admin","password":"admin123"}'
# → 200 with { "token": ..., "user": { ... } }
```

Then open `https://your-domain.com/docs` to confirm the Swagger UI + docs are served.

### 7.5 Gotchas

- **Rate limiting:** login allows **5 attempts/min per username+IP** — repeated bad logins during device testing will hit `429`. Back off briefly, don't treat it as a bug.
- **Avatars:** without `storage:link`, avatar URLs 404. Run it (or publish symlinks on shared hosts).
- **CORS:** native mobile apps don't enforce CORS, so nothing to configure for the app itself. Laravel 13 ships CORS defaults for any future web dashboard.
- **`/up` health endpoint** is enabled via `withRouting(health: '/up')` — use it for uptime monitoring.
- **`npm ci` needs a `package-lock.json`** — if it's missing on your clone, use `npm install` instead.
