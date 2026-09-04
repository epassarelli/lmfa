# Design

## Scope

Composer dry-run resolves five compatible package updates:

- `guzzlehttp/guzzle` 7.8.1 to 7.15.5
- `guzzlehttp/promises` 2.0.2 to 2.5.3
- `guzzlehttp/psr7` 2.6.2 to 2.13.1
- `symfony/deprecation-contracts` 3.5.0 to 3.7.1
- `symfony/polyfill-php80` 1.29.0 to 1.37.0

The change is lockfile-only. Laravel's HTTP facade keeps its public interface,
and connector tests fake all remote calls.

## Validation

- Run Composer audit before and after the update.
- Run the connector and image source feature tests that use Laravel HTTP.
- Run the public quality suite to guard the active web surface.

## Deferred Risk

The remaining advisories include Laravel and Symfony packages that require a
coordinated Laravel 11+ migration. That migration is intentionally out of scope
and must be planned separately.
