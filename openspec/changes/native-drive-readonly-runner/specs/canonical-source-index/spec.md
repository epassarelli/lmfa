## ADDED Requirements

### Requirement: The project SHALL publish a canonical source index for repo and Drive artifacts
The system SHALL document which files in Git are canonical for functional and technical decisions and which Google Drive artifacts remain operational-only sources.

#### Scenario: Contributor needs the repo source of truth
- **WHEN** a contributor reads the canonical source index
- **THEN** they can identify the exact canonical files under `project/docs/`
- **AND** they can see the correct filenames currently present in the repository

#### Scenario: Contributor needs the operational backlog source
- **WHEN** a contributor reads the canonical source index
- **THEN** they can see that the active daily backlog lives in Google Drive, sheet `Backlog`
- **AND** they can distinguish that operational source from the canonical Git documentation set
