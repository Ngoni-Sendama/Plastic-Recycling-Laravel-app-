# Offline And Online Sync

## Goal

Allow the mobile app to work offline and sync with the Laravel API when internet access returns.

The app should support both:

- Pulling latest server records.
- Pushing locally created or changed records.

## Required Mobile Changes

The current mobile app depends on live API calls. Offline support requires local storage.

Recommended mobile storage options:

- SQLite through Expo SQLite.
- WatermelonDB if the app becomes more complex.
- Realm if richer offline sync is needed.

For this app, Expo SQLite is likely enough.

## Required Local Tables

The mobile app should keep local versions of:

- users or current user profile
- materials
- material_intakes
- crushing_productions
- dispatches
- palletizing_receipts
- palletizing_productions
- pellet_sales
- cash_remittances
- sync_queue
- sync_state

## Sync Metadata Fields

Each syncable record should include:

- local_id
- server_id nullable
- created_at
- updated_at
- deleted_at nullable
- last_synced_at nullable
- sync_status
- sync_error nullable

Suggested `sync_status` values:

- synced
- pending_create
- pending_update
- pending_delete
- failed

## Push Sync

The app sends locally pending changes to Laravel.

Suggested endpoint:

```http
POST /api/sync/push
```

Request shape:

```json
{
  "changes": {
    "material_intakes": [],
    "crushing_productions": [],
    "dispatches": [],
    "palletizing_receipts": [],
    "palletizing_productions": [],
    "pellet_sales": [],
    "cash_remittances": []
  }
}
```

Laravel response should map local IDs to server IDs:

```json
{
  "accepted": [
    {
      "table": "material_intakes",
      "local_id": "local-uuid",
      "server_id": 123
    }
  ],
  "rejected": []
}
```

## Pull Sync

The app requests changes since the last successful sync.

Suggested endpoint:

```http
GET /api/sync/pull?since=2026-07-31T10:00:00Z
```

Response shape:

```json
{
  "server_time": "2026-07-31T10:15:00Z",
  "changes": {
    "materials": [],
    "material_intakes": [],
    "crushing_productions": [],
    "dispatches": [],
    "palletizing_receipts": [],
    "palletizing_productions": [],
    "pellet_sales": [],
    "cash_remittances": []
  },
  "deleted": {
    "material_intakes": []
  }
}
```

## Conflict Handling

Do not silently overwrite important operational records. Stock, production, sales, and cash records need auditability.

Use version-based conflict detection.

Each syncable server record should include:

- `id`
- `updated_at`
- `deleted_at` nullable
- `lock_version` integer, default `1`
- `created_by_user_id`
- `updated_by_user_id` nullable

Each local mobile record should store:

- `server_id` nullable
- `server_lock_version` nullable
- `last_synced_at` nullable
- `sync_status`
- `updated_locally_at`

When the mobile app pulls a record, it stores the current server `lock_version`.

When the mobile app pushes an update, it must send:

- Record server ID
- Local ID
- The `server_lock_version` the mobile app edited from
- Changed values
- Local edit timestamp
- User who made the change

Laravel should compare the submitted `server_lock_version` with the current server record's `lock_version`.

If the versions match:

- Accept the update.
- Recalculate computed fields.
- Increment `lock_version`.
- Update `updated_by_user_id`.
- Return the new server version to the mobile app.

If the versions do not match:

- Do not overwrite the server record.
- Mark the pushed change as a conflict.
- Store it in a `sync_conflicts` table.
- Return a conflict response to the mobile app.
- Keep the local record in a conflict or failed-sync state until resolved.

## Sync Conflicts Table

Create a `sync_conflicts` table for admin review.

Suggested columns:

- id
- table_name
- record_id
- local_id nullable
- submitted_by_user_id
- server_version
- submitted_version
- server_payload JSON
- submitted_payload JSON
- changed_fields JSON nullable
- status
- resolution nullable
- resolved_by_user_id nullable
- resolved_at nullable
- timestamps

Suggested `status` values:

- pending
- resolved
- discarded

Suggested `resolution` values:

- keep_server
- accept_submitted
- manual_merge
- discard_submitted

## Super Admin Conflict Review

Conflicts should be reviewed in the Filament admin panel by a super admin or supervisor.

The conflict screen should show:

- The current server record.
- The submitted offline/mobile change.
- User who made the server change.
- User who submitted the conflicting change.
- Server update timestamp.
- Mobile/local edit timestamp.
- Changed fields highlighted.

The reviewer should be able to choose:

- Keep the server version.
- Accept the submitted mobile version.
- Manually merge selected fields.
- Discard the submitted change.

After resolution:

- Update the real record if needed.
- Increment `lock_version` if the real record changes.
- Mark the conflict as resolved or discarded.
- Include the resolved server record in the next pull sync.
- Clear the affected mobile pending/conflict state after the app pulls the resolved record.

## Recommended Conflict Policy

Use different policies depending on record importance:

- Cash, sales, remittance, stock movement, and production records: require admin review.
- Low-risk notes or metadata: latest update can win if the business accepts that.
- Deletes: require extra care. If a record was changed on the server after the mobile app last synced, do not delete automatically; create a conflict.

For this app, the default should be admin review, not automatic overwrite.

## UX Requirements

The mobile app should show:

- Online/offline status.
- Pending sync count.
- Last synced time.
- Failed sync entries.
- Manual sync button.

Users should still be able to create records while offline.

## Backend Requirements

Laravel should support:

- Stable `updated_at` timestamps.
- Soft deletes for syncable records.
- Bulk push endpoint.
- Pull endpoint filtered by timestamp.
- Validation per record.
- User attribution for pushed records.

## Implementation Notes

- Generate UUID local IDs on the mobile app.
- Avoid relying only on auto-increment IDs while offline.
- Keep sync operations idempotent where possible.
- Do not delete local records immediately; mark them deleted until the server confirms.
- Make dashboard work from local data when offline and refresh from server when online.
