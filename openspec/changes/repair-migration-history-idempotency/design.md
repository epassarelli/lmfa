# Design

`Schema::hasTable` is checked before the existing `Schema::create` call. Laravel
will record the migration after the no-op return, allowing the remaining ordered
migrations to proceed. Fresh databases still create the intended table.

This is safer than inserting a row into the `migrations` table because it keeps
the migration executable and validates the normal Laravel workflow.
