## ADDED Requirements

### Requirement: Windows scheduler SHALL be configured natively and remain disabled by default
The system SHALL provide a native Windows Scheduled Task definition for the MFA backlog runner and SHALL leave that task disabled after registration until a human explicitly enables it.

#### Scenario: Disabled task is registered
- **WHEN** the operator registers the scheduled task for the read-only runner
- **THEN** Windows stores the task natively with the configured trigger and action
- **AND** the task remains disabled after registration

#### Scenario: Scheduled action uses the read-only runner
- **WHEN** an operator inspects the registered task
- **THEN** the task action points to the native PowerShell backlog runner
- **AND** the arguments invoke the read-only backlog mode instead of a write-capable flow
