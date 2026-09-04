# Security HTTP Client Hardening

## Why

The lockfile contains vulnerable Guzzle HTTP client packages. Composer can update
them to patched versions without changing Laravel, application code, or package
major versions.

## What Changes

- Update Guzzle, PSR-7, promises, and their compatible support dependencies.
- Validate the Laravel HTTP client flows with existing faked HTTP feature tests.
- Record the remaining framework-level security remediation as a separate,
  higher-impact Laravel migration decision.

## Non-Goals

- Upgrade Laravel 10 to Laravel 11 or later.
- Change outbound connector behavior, credentials, or production configuration.
