# Repair Migration History Idempotency

## Why

The local development database contains `data_deletion_requests` with the exact
schema expected by its pending migration, but no migration history row. Running
the standard migration command therefore fails before later pending migrations
can synchronize the environment.

## What Changes

- Make the table-creation migration idempotent when the table already exists.
- Preserve the existing schema and data in environments that received the table
  before its migration history row was recorded.

## Non-Goals

- Delete, recreate, or alter existing data-deletion records.
- Mark migration history manually through SQL.
