## ADDED Requirements

### Requirement: Canonical radio and signal directory
The system SHALL manage every radio or signal as a canonical evergreen entity separate from legacy records, News and temporal Events. A published signal MUST have a name, unique slug, editorial description, one or more transmission modes, source references, internal verifier and current verification date.

#### Scenario: Publish a verified hybrid signal
- **WHEN** an authorized editor publishes a signal with air and digital channels, sources and current verification
- **THEN** the system SHALL make one canonical signal eligible for public discovery.

#### Scenario: Reject an incomplete signal
- **WHEN** an editor attempts to publish a signal without a verified listening channel or the location/frequency required for air transmission
- **THEN** the system SHALL reject publication and identify the missing requirements.

### Requirement: Multiple listening channels
The system SHALL allow a signal to have one or more explicit listening channels, each with a channel type, platform, URL or frequency, label and editorial status. The public interface MUST present only verified active channels.

#### Scenario: Show official ways to listen
- **WHEN** a verified signal has an FM frequency, official website and direct stream URL
- **THEN** the profile SHALL display each approved channel with an explicit way to listen or visit.

### Requirement: Territorial and platform discovery
The system SHALL provide a paginated public directory with search and filters by province, locality, transmission mode and platform. Arbitrary search combinations MUST not become low-value indexable pages.

#### Scenario: Filter digital signals in a province
- **WHEN** a visitor filters by a province and streaming mode
- **THEN** the directory SHALL return only verified published signals that match both conditions and preserve the filters in pagination.

### Requirement: Useful public radio profile
The system SHALL render a signal profile with coverage and location, frequency where applicable, contact information, channels, map when coordinates exist, sources and verification date. The profile MUST expose only its approved current information.

#### Scenario: Render a local FM profile
- **WHEN** a visitor opens a verified FM profile with location and coordinates
- **THEN** the profile SHALL show province, locality, address, frequency, contact and a map link or map representation.

### Requirement: Editorial management and release gate
The system SHALL provide authenticated backoffice and API management for signals and channels, with publication, unpublication, archive, preview and a read-only quality audit. Automated ingestion MUST remain disabled until a controlled editorial pilot is approved.

#### Scenario: Block automated publication before the gate
- **WHEN** an automated ingestion attempts to create or publish a signal before release approval
- **THEN** the system SHALL not expose that signal publicly.
