## ADDED Requirements

### Requirement: Published news is reflected in cached listings

The application SHALL invalidate cached news listings when a news record is
saved or deleted.

#### Scenario: New published news on the home page

- **GIVEN** the cached home news collection contains an older value
- **WHEN** a published news record is saved
- **THEN** the next home response SHALL render the newly published record

#### Scenario: News update or deletion

- **WHEN** a news record is updated or deleted
- **THEN** the home, news sidebar, and festival related-news caches SHALL be
  invalidated
