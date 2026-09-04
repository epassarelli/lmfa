## ADDED Requirements

### Requirement: Layouts free of visible debug output
Active public and administrative layouts SHALL NOT emit versioned browser debug messages unrelated to user-facing behavior.

#### Scenario: Rendering active layouts
- **WHEN** an active layout or shared selector is rendered
- **THEN** it contains no literal debug `console.log` messages
