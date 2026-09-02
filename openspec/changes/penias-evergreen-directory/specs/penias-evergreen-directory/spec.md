## ADDED Requirements

### Requirement: Canonical evergreen Peña profile
The system SHALL manage each Peña as a canonical, permanent cultural venue distinct from legacy records, News, and temporal Events. A publishable profile MUST contain a canonical name, unique slug, editorial description, normalized location, venue type, editorial status, verification status, source references, and last verification date.

#### Scenario: Publish a verified Peña profile
- **WHEN** an authorized editor publishes a profile that satisfies the required editorial and verification fields
- **THEN** the system SHALL make that canonical profile eligible for public visibility.

#### Scenario: Reject an incomplete publication
- **WHEN** an authorized editor attempts to publish a profile without verified location, source references, or last verification date
- **THEN** the system SHALL reject publication and identify the missing requirements.

### Requirement: Territorial public directory
The system SHALL provide a public national Peña directory with paginated discovery by text, province, locality, and venue type. The directory MUST expose only profiles that are published and valid for public visibility.

#### Scenario: Filter published Peñas by territory
- **WHEN** a visitor filters the directory by province or locality
- **THEN** the system SHALL return only matching published Peña profiles and preserve the filter in pagination links.

#### Scenario: Hide non-public profiles
- **WHEN** a visitor requests the directory or a Peña URL for a draft, archived, future, or non-verifiable profile
- **THEN** the system SHALL not expose the profile publicly.

### Requirement: Separation from temporal event programming
The system SHALL maintain Peña identity separately from event programming. A Peña MAY be related to Events only through an explicit persistent relation, and its public profile MUST display only related Events that are published and future-dated.

#### Scenario: Show future events for a Peña
- **WHEN** a published Peña has explicitly related Events with future dates and public visibility
- **THEN** the Peña profile SHALL display those Events as upcoming programming.

#### Scenario: Exclude expired or unpublished events
- **WHEN** a related Event is past-dated, unpublished, archived, or otherwise not publicly visible
- **THEN** the Peña profile SHALL not present it as current programming.

### Requirement: Editorial management and controlled ingestion
The system SHALL provide authenticated administration and API surfaces to create, update, review, publish, archive, and audit Peña profiles. Automated editorial ingestion MUST remain disabled until the release gate and controlled pilot are completed.

#### Scenario: Authorized API update
- **WHEN** an authenticated and authorized editorial client sends a valid update for a Peña profile
- **THEN** the system SHALL persist the permitted changes and return a stable response.

#### Scenario: Block unapproved automation
- **WHEN** an automated bulk ingestion is attempted before the module release gate is approved
- **THEN** the system SHALL prevent public publication through that ingestion path.

### Requirement: Quality audit and pilot publication gate
The system SHALL provide a quality audit that evaluates identity, content, location, contact or official channel, type-specific fields, media, SEO, sources, verification freshness, and editorial relations. The public release MUST require a controlled pilot of at least ten verified profiles with authorized media and sources.

#### Scenario: Prioritize an incomplete profile
- **WHEN** the audit identifies a published or candidate profile with missing required quality signals
- **THEN** the system SHALL report the missing signals and its quality status for editorial prioritization.

#### Scenario: Prevent release without a verified pilot
- **WHEN** the directory has fewer than ten verified pilot profiles or lacks the required release checks
- **THEN** the system SHALL keep the module out of broad public activation and sitemap inclusion.

### Requirement: Search-engine-safe public representation
The system SHALL render every public Peña profile with canonical URL, descriptive metadata, breadcrumbs, appropriate structured data, and sitemap inclusion only when the profile is published and verified. Arbitrary filter combinations MUST NOT create low-value indexable URLs.

#### Scenario: Index a verified public profile
- **WHEN** a published and verified Peña profile is rendered publicly
- **THEN** the system SHALL emit its canonical URL and eligible structured metadata and include it in the relevant sitemap.

#### Scenario: Canonicalize arbitrary filters
- **WHEN** a visitor uses an arbitrary free-text or empty-result filter combination
- **THEN** the system SHALL retain a canonical URL that does not create a separate low-value indexable landing.

### Requirement: Legacy data preservation and safe transition
The system SHALL preserve legacy Peña records for read-only audit until an approved migration maps them to canonical profiles. The system MUST NOT automatically publish, delete, or redirect a legacy record without an audited equivalence.

#### Scenario: Audit a legacy record before migration
- **WHEN** an editor evaluates a legacy Peña record
- **THEN** the system SHALL retain its origin information and require verification before creating or publishing a canonical profile.

#### Scenario: Redirect only a verified equivalence
- **WHEN** a legacy URL has an approved one-to-one canonical replacement
- **THEN** the system SHALL allow a permanent redirect to the canonical Peña URL.
