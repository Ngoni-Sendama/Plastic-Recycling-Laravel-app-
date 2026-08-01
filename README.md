 
  - [x] Policies/permissions by role.
      - Admin, stock controller, crusher operator, receiver, palletizing operator, supervisor.
      - Built with Filament Shield (bezhansalleh/filament-shield ^4.3): HasRoles on User, plugin registered, 11 policies generated.
      - RolePermissionSeeder: 6 business roles + super_admin (139 permissions total), assigned to all 6 seeded staff via syncRoles.
      - Verification: db:seed runs clean, 61 API/service tests passed (389 assertions), Pint clean.

  - [x] API work from 03-laravel-api-mobile-sync.md.
      - Auth API
      - Users API
      - Materials API
      - Workflow endpoints
      - Dashboard API
      - Endpoints: 32 routes registered (Auth 3, Users 5, Materials 3, Workflow 14, Dashboard 1, Sync 2) + 4 Reports endpoints.
      - Verification: covered by 61 API/service tests (389 assertions), Pint clean.

  - [x] Sanctum auth from 04-sanctum-auth.md.
      - POST /api/login
      - POST /api/logout
      - GET /api/user
      - username/password login controller
      - rate limiting login attempts (throttle:api-login)
      - Verification: AuthTest passes; token-based auth, no password hashes exposed.

  - [x] Offline sync from 05-offline-online-sync.md.
      - lock_version
      - soft deletes on syncable tables
      - sync_conflicts table
      - /api/sync/pull
      - /api/sync/push
      - conflict review in Filament (SyncConflictsResource)
      - Verification: SyncApiTest + SyncConflictResolverTest pass.

  - [x] Reports pages (Filament + API):
      - Stock Summary
      - Production Summary
      - Sales Summary
      - Cash Reconciliation
      - Endpoints: GET /api/reports/stock, /api/reports/production, /api/reports/sales, /api/reports/cash-reconciliation (Bearer auth, optional from/to date filters, `{ "data": ... }` envelope)
      - Verification: 61 API/service tests passed (389 assertions, incl. 13 report tests), Pint clean.

 