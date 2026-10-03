## ADDED Requirements

### Requirement: Native runner SHALL read the active Google Drive backlog without Laravel or codex exec
The system SHALL provide a native Windows runner that reads the MFA backlog from the configured Google Sheets spreadsheet in read-only mode and SHALL NOT depend on `php artisan mfa:orchestrate-backlog` or `codex exec`.

#### Scenario: Read-only backlog probe succeeds
- **WHEN** the operator runs the native runner in `read_backlog` mode with valid Google OAuth refresh-token credentials
- **THEN** the runner reads the configured `Backlog` sheet from Google Sheets API with read-only scope
- **AND** it writes a local structured summary without mutating Drive

#### Scenario: Missing credentials block the probe safely
- **WHEN** the operator runs the native runner without the required OAuth environment variables
- **THEN** the runner stops before calling Drive
- **AND** it returns a structured blocked result explaining which configuration is missing

### Requirement: Native runner SHALL support a local self-test without network access
The system SHALL provide a self-test mode that validates paths, task wiring, expected spreadsheet target, and local log destinations without requiring a live Google request.

#### Scenario: Self-test validates local wiring only
- **WHEN** the operator runs the native runner in `self_test` mode
- **THEN** the runner verifies script paths, output directories, expected spreadsheet identifiers, and task command preview
- **AND** it does not call Google APIs or write to Drive

### Requirement: Native runner SHALL detect backlog shape drift before producing a summary
The system SHALL validate the expected sheet name and required headers before reporting a read-only result from Drive.

#### Scenario: Required headers are present
- **WHEN** the live backlog read returns the configured header row
- **THEN** the runner confirms the required columns for ID, project, task, status, delegation state, priority, and notes
- **AND** only then produces the local summary

#### Scenario: Header drift blocks the run
- **WHEN** the backlog tab is renamed or the required headers are missing
- **THEN** the runner returns a structured blocked result
- **AND** it does not pretend the backlog was read successfully
