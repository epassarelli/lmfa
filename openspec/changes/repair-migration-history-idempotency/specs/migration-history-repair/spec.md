## ADDED Requirements

### Requirement: Existing data deletion schemas do not block migration

The migration that introduces `data_deletion_requests` SHALL complete without
error when the table already exists.

#### Scenario: Pre-existing matching table

- **WHEN** an environment has `data_deletion_requests` but lacks this migration
  in its history
- **THEN** `php artisan migrate` SHALL record the migration without changing or
  deleting the existing table

#### Scenario: Fresh database

- **WHEN** an environment does not have `data_deletion_requests`
- **THEN** the migration SHALL create the table with its defined schema
