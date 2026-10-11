## ADDED Requirements

### Requirement: Any authenticated user can request a link
The system SHALL let any authenticated user, regardless of roles or of how they logged in, request to administer an Interprete or Festival by providing their relationship with it and a message.

#### Scenario: User without roles
- **GIVEN** a user with no roles
- **WHEN** the user opens "Mis fichas" and submits a request
- **THEN** the request SHALL be stored as `pending`.

#### Scenario: Guest
- **WHEN** an unauthenticated visitor opens "Mis fichas"
- **THEN** they SHALL be redirected to login.

### Requirement: Request validation and abuse limits
The system MUST require a registered entity type, an existing entity, a relationship from the allowed list and a message of 10 to 1000 characters. The system MUST NOT allow a second pending request by the same user for the same entity, more than 5 pending requests per user, or a request for an entity the user already administers.

#### Scenario: Duplicate pending request
- **WHEN** the user requests the same entity twice
- **THEN** the second request SHALL be rejected and only one SHALL exist.

#### Scenario: Pending limit
- **GIVEN** a user with 5 pending requests
- **WHEN** they submit another
- **THEN** it SHALL be rejected.

#### Scenario: Already manager
- **WHEN** a user who administers an entity requests it
- **THEN** the request SHALL be rejected.

### Requirement: My entities
The system SHALL show the user the entities they administer, with links to edit them, and their requests with status, and SHALL let them cancel their own pending requests but not other users' requests.

#### Scenario: Cancel own request
- **WHEN** the user cancels their pending request
- **THEN** its status SHALL become `cancelled`.

#### Scenario: Cancel another user's request
- **WHEN** a user tries to cancel a request that is not theirs
- **THEN** the response SHALL be forbidden.

### Requirement: Administrator review
The system SHALL let only administrators list requests by status, view their detail, approve them with a membership role, and reject them with a note. Reviews MUST record reviewer and date and only apply to `pending` requests.

#### Scenario: Approve creates access
- **GIVEN** a pending request for an interprete with no organization
- **WHEN** the administrator approves it
- **THEN** the interprete's primary organization SHALL exist, the user SHALL be an active member with the chosen role, the request SHALL be `approved`, and the user SHALL be able to manage the interprete.

#### Scenario: Reuse the active organization
- **GIVEN** an entity already linked to an active organization
- **WHEN** a request is approved
- **THEN** the user SHALL be added to that organization and no new organization SHALL be created.

#### Scenario: Reject
- **WHEN** the administrator rejects a pending request with a note
- **THEN** the status SHALL be `rejected`, no membership SHALL be created and the user SHALL NOT gain access.

#### Scenario: Already reviewed
- **WHEN** an approved or rejected request is reviewed again
- **THEN** the action SHALL be refused.

#### Scenario: Inactive organization
- **GIVEN** the entity's only organization is inactive
- **WHEN** the administrator approves a request
- **THEN** the approval SHALL be refused with an explanatory message.

#### Scenario: Collaborator reviewing
- **WHEN** a non-administrator opens the claims listing or tries to approve
- **THEN** the response SHALL be forbidden.
