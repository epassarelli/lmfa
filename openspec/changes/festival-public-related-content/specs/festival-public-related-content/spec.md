## ADDED Requirements

### Requirement: Public explicit festival relationships
The festival detail SHALL render public explicitly linked Events, active Artists, visible evergreen Articles and published News irrespective of the Festival Vivo flag and allowlist. Empty modules SHALL remain hidden. Integration SHALL preserve public Event relations introduced by `event-detail-navigation-and-relations`, and Artist detail rollout gates SHALL remain unchanged.

#### Scenario: Festival outside the pilot
- **GIVEN** a published Festival with eligible explicit relationships
- **WHEN** its detail is requested with the flag disabled and an empty allowlist
- **THEN** all eligible modules and canonical destination links SHALL be visible.

#### Scenario: Hidden or absent content
- **GIVEN** draft, scheduled publication, inactive artist, inactive or past event, deleted evergreen or absent relationships
- **WHEN** the detail is rendered
- **THEN** ineligible content SHALL not appear, unrelated items SHALL not be inferred and empty headings SHALL not appear.

### Requirement: Access to every upcoming date
Upcoming public active Events SHALL be ordered by start time and ID, show the event title and time, and be paginated with three records per page using `eventos_page` on the existing festival URL.

#### Scenario: Festival has more than three upcoming dates
- **GIVEN** a Festival with five eligible nights
- **WHEN** the visitor follows the pagination
- **THEN** all five SHALL be reachable, with the same festival canonical, one H1 and `noindex,follow` on subsequent pages.

### Requirement: Bounded queries and preserved navigation
The detail SHALL query each related collection once with SQL bounds, preserve related-image fallbacks and the existing three province/month recommendations, and render server-side HTML links without added global JS.

#### Scenario: Festival relations grow
- **WHEN** more relationships are added
- **THEN** initial rendering SHALL remain bounded and SHALL not introduce per-item lazy loading.
