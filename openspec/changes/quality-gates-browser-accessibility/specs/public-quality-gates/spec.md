## ADDED Requirements

### Requirement: Accessible public page structure
Critical public pages SHALL render a document language, a single primary heading, a main landmark and navigation controls with accessible labels.

#### Scenario: Visitor opens a critical landing
- **WHEN** a critical public landing renders successfully
- **THEN** its HTML includes the required structural accessibility markers

### Requirement: Public response budget
Critical public landing responses SHALL remain within a documented HTML response budget.

#### Scenario: Landing response grows beyond budget
- **WHEN** a critical landing exceeds its configured HTML budget
- **THEN** the quality gate fails with the measured response size
