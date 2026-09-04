## ADDED Requirements

### Requirement: Public availability healthcheck
The system SHALL expose a public healthcheck that verifies application and database availability without disclosing sensitive runtime information.

#### Scenario: Healthy application and database
- **WHEN** the application can execute a minimal database query
- **THEN** the endpoint returns HTTP 200 with a stable healthy status payload

#### Scenario: Unavailable database
- **WHEN** the database check fails
- **THEN** the endpoint returns HTTP 503 with a stable degraded status payload and logs the diagnostic detail

### Requirement: Authenticated operational diagnosis
The system SHALL provide administrators a diagnosis of database, cache, scheduler freshness and queue configuration.

#### Scenario: Administrator opens diagnosis
- **WHEN** an authenticated administrator requests the diagnosis
- **THEN** the system returns the state of each check without secrets or exception traces

#### Scenario: Non-administrator requests diagnosis
- **WHEN** a non-administrator requests the diagnosis
- **THEN** the system denies access

### Requirement: Scheduler freshness signal
The system SHALL record a scheduler heartbeat that allows diagnosis to identify stale scheduler execution.

#### Scenario: Scheduler runs normally
- **WHEN** the Laravel scheduler starts its scheduled work
- **THEN** the heartbeat is refreshed with the current timestamp

#### Scenario: Scheduler is stale
- **WHEN** the stored heartbeat exceeds the configured freshness threshold
- **THEN** the diagnosis reports scheduler status as degraded
