## ADDED Requirements

### Requirement: Social channels and contact stage
The system SHALL store on each organization an optional Instagram and Facebook reference (handle or URL), a contact stage, the date of the last contact and free notes. The stage MUST be one of `prospect`, `contacted`, `replied`, `email_obtained`, `declined` and defaults to `prospect`.

#### Scenario: Contact only through Instagram
- **WHEN** an administrator saves an organization with an Instagram handle and no email
- **THEN** the organization SHALL be saved with stage `prospect` or the chosen stage and SHALL NOT be considered contactable by email.

#### Scenario: Invalid stage
- **WHEN** a request sends a stage outside the allowed list
- **THEN** validation SHALL fail.

### Requirement: Social links
The system SHALL show Instagram and Facebook references in the organization listing as links that open the corresponding profile, building the URL from a bare handle when needed.

#### Scenario: Handle with at sign
- **WHEN** an organization stores `@artista.demo` as Instagram
- **THEN** the link SHALL point to `https://www.instagram.com/artista.demo`.

### Requirement: Register contact
The system SHALL let the administrator register a contact in one action that sets the last contact date to now and, only when the stage is `prospect`, moves it to `contacted`.

#### Scenario: First contact
- **GIVEN** an organization in stage `prospect`
- **WHEN** the administrator registers a contact
- **THEN** `last_contacted_at` SHALL be set and the stage SHALL become `contacted`.

#### Scenario: Later stage preserved
- **GIVEN** an organization in stage `replied`
- **WHEN** the administrator registers a contact
- **THEN** only `last_contacted_at` SHALL change.

### Requirement: Stage filter
The system SHALL let the administrator filter the organization listing by contact stage.

### Requirement: Export contactable organizations
The system SHALL export to CSV only organizations that are active, have consent, have not unsubscribed and have a non-empty email, honoring the listing's search and type filters. Cells that start with a formula character MUST be neutralized. The export MUST be restricted to administrators.

#### Scenario: Exclusions
- **WHEN** the export runs
- **THEN** inactive, unsubscribed, no-consent and no-email organizations SHALL NOT appear.

#### Scenario: Formula injection
- **WHEN** a name starts with `=`
- **THEN** the CSV cell SHALL be prefixed so spreadsheets do not execute it.

#### Scenario: Collaborator
- **WHEN** a collaborator requests the export
- **THEN** the response SHALL be forbidden.
