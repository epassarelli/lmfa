## ADDED Requirements

### Requirement: Patched HTTP client dependencies

The application SHALL use patched compatible versions of the Guzzle HTTP client
package family without changing the Laravel major version.

#### Scenario: Composer dependency audit

- **WHEN** Composer audits the locked dependencies after this change
- **THEN** it SHALL report no advisory affecting `guzzlehttp/guzzle`,
  `guzzlehttp/promises`, or `guzzlehttp/psr7`

### Requirement: Outbound HTTP integrations remain testable

Existing integrations using Laravel's HTTP facade SHALL continue to be covered
by feature tests with faked remote responses.

#### Scenario: Connector test suite

- **WHEN** the connector feature tests run
- **THEN** they SHALL complete without performing live network calls
