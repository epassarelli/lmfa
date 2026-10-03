## ADDED Requirements

### Requirement: Public recipes home must render without undefined route state
The public recipes home MUST render successfully without depending on alphabet-route state that is unavailable in the index action.

#### Scenario: Recipes home responds successfully
- **GIVEN** there are published recipes available
- **WHEN** a visitor requests `GET /recetas-de-comidas-tipicas-argentinas`
- **THEN** the response status is `200`
- **AND** the page renders the public recipes home content

### Requirement: Public recipes home must show published recipe collections
The public recipes home MUST build its featured collections using stable cache keys scoped to the home route.

#### Scenario: Featured collections are resolved from home cache keys
- **GIVEN** the recipes home needs latest and most visited published recipes
- **WHEN** the controller resolves cached collections for the home route
- **THEN** it uses dedicated cache keys for the home route
- **AND** it does not reference letter-route variables that are unavailable in `index()`
