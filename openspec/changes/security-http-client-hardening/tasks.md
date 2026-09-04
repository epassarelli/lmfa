## 1. Dependency hardening

- [x] 1.1 Determine the compatible update set with Composer dry-run.
- [x] 1.2 Update only the compatible Guzzle package family in `composer.lock`.

## 2. Validation and traceability

- [ ] 2.1 Run affected HTTP integration and public quality tests. Connector and public quality suites pass; news media tests are blocked by pending local schema migration `2026_08_31_170000_add_source_metadata_to_media_assets`.
- [x] 2.2 Audit the updated lockfile and document the remaining framework migration risk: 43 advisories affecting 15 packages remain, including Laravel/Symfony packages that require a coordinated Laravel 11+ plan.
