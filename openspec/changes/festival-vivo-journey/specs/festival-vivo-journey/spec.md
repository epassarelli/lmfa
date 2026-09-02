## ADDED Requirements

### Requirement: Controlled Festival journey
The system SHALL expose related Festival, Event, and Artist journey modules only when the festival journey flag is enabled and the root Festival is in the configured allowlist.

#### Scenario: Disabled journey preserves current pages
- **WHEN** the feature flag is disabled
- **THEN** Festival, Event, and Artist public pages SHALL not render journey modules.

#### Scenario: Allowlisted Festival enables its journey
- **WHEN** an allowlisted published Festival is rendered while the flag is enabled
- **THEN** the system SHALL render only its eligible related journey modules.

### Requirement: Canonical related content visibility
The system SHALL use only explicit persisted relations and canonical public visibility rules for journey content. Future Events SHALL be ordered by `start_at` ascending; inactive, past, draft, future-published, or inactive-artist content MUST be excluded.

#### Scenario: Festival displays eligible upcoming events
- **WHEN** a visible allowlisted Festival has related public future Events
- **THEN** the system SHALL display at most three Events ordered by their start date.

#### Scenario: Empty relationships do not render modules
- **WHEN** no related content passes the visibility rules
- **THEN** the system SHALL render neither the module heading nor a fallback unrelated item.

### Requirement: Journey links remain accessible and measurable
The system SHALL render journey links as canonical server-side HTML links with descriptive accessible labels and journey metadata. Analytics MUST not send personal data.

#### Scenario: Visitor selects a journey destination
- **WHEN** a visitor activates a journey link
- **THEN** the link SHALL navigate through its canonical href and be eligible for a single `select_content` analytics event.

### Requirement: Search-safe and performant journey rendering
The system SHALL retain each page canonical URL and single H1, load journey relations eagerly with SQL limits, and prevent lazy loading regressions in automated tests.

#### Scenario: Related collection size changes
- **WHEN** an eligible Festival has between one and six related records
- **THEN** rendering SHALL use bounded collections without per-item lazy-loaded queries.
