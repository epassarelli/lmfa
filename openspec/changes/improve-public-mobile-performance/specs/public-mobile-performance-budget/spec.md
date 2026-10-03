## ADDED Requirements

### Requirement: Public homes and indexes must prioritize mobile-first initial payloads
High-traffic public homes and index routes MUST limit the first server-side payload to a bounded number of records that favors mobile rendering and progressive discovery.

#### Scenario: Public home resolves a reduced initial collection
- **GIVEN** a public home or listing route with paginated or cached editorial cards
- **WHEN** the first response is rendered for mobile and desktop users
- **THEN** the controller returns only the minimum useful initial set for discovery
- **AND** deeper exploration remains available through pagination, section routes or existing filters

### Requirement: Secondary editorial blocks must not duplicate large payloads on the same response
Public index routes MUST keep sidebar and related editorial collections smaller than the main list when they are rendered in the same request.

#### Scenario: Secondary blocks stay smaller than the main listing
- **GIVEN** a public index route renders a main collection plus sidebars or related sections
- **WHEN** the route resolves cached secondary blocks
- **THEN** those secondary blocks use smaller limits than the primary list
- **AND** they avoid repeating oversized editorial payloads that are already available elsewhere in navigation

### Requirement: Legacy public sections must apply the same performance budget without redesign
Legacy public sections that still need future UX homogenization MUST still adopt smaller server-side collections immediately, without waiting for a full redesign.

#### Scenario: Legacy sections reduce secondary collections
- **GIVEN** a legacy public section such as myths or recipes
- **WHEN** the controller builds latest, most viewed or alphabetical collections
- **THEN** it uses reduced collection sizes aligned with the mobile-first performance budget
- **AND** it preserves current routes, templates and navigation behavior
