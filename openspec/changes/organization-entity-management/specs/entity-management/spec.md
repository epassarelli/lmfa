## ADDED Requirements

### Requirement: Organization to entity links
The system SHALL allow an organization to be linked to any number of portal entities (Interprete, Festival, PeniaProfile, RadioSignal) and an entity to be linked to any number of organizations. The pair (organization, entity) MUST be unique.

#### Scenario: Production company with several artists
- **WHEN** one organization is linked to three interpretes
- **THEN** each interprete SHALL list that organization and the organization SHALL list the three interpretes.

#### Scenario: Duplicate link
- **WHEN** the same organization and entity are linked twice
- **THEN** the second attempt SHALL NOT create another row.

### Requirement: Membership grants write permission
The system SHALL let a user manage an entity when the user is an `active` member with role `owner` or `editor` of an `active` organization linked to that entity. Members with role `member`, and memberships `pending` or `inactive`, MUST NOT grant write access.

#### Scenario: Editor of the organization
- **GIVEN** a user with an active `editor` membership in an organization linked to interprete A
- **WHEN** the user updates interprete A
- **THEN** the action SHALL be authorized.

#### Scenario: Unrelated entity
- **WHEN** the same user updates interprete B, not linked to any of their organizations
- **THEN** the action SHALL be denied.

#### Scenario: Pending membership
- **WHEN** a user with a `pending` membership updates a linked entity
- **THEN** the action SHALL be denied.

#### Scenario: Several administrators
- **WHEN** two users are active members of the same organization linked to an entity
- **THEN** both SHALL be able to manage it.

### Requirement: Administrator and legacy access preserved
The system SHALL keep full access for the `administrador` role and SHALL keep the legacy rule where a user who is the recorded `user_id` of an entity can still edit it.

#### Scenario: Legacy creator
- **WHEN** a collaborator who created an interprete (its `user_id` is theirs) edits it
- **THEN** the action SHALL be authorized.

### Requirement: Scoped backoffice listings
The Festival backoffice listing SHALL show `colaborador` and `prensa` users the festivals they created plus those they manage. Where `entity_management.scope_listings` is enabled, the Interprete listing SHALL do the same for non-administrators. Neither SHALL use per-row queries.

#### Scenario: Collaborator listing
- **WHEN** a collaborator with one managed interprete opens the interpretes listing with scoping enabled
- **THEN** only that interprete (plus those they created) SHALL appear.

#### Scenario: Scoping disabled
- **WHEN** the flag is disabled
- **THEN** the interpretes listing SHALL behave as before.

#### Scenario: Administrator listing
- **WHEN** an administrator opens the listing
- **THEN** all entities SHALL appear regardless of scoping.

### Requirement: Contact consent and verification metadata
The system SHALL record on an organization whether and when contact for promotional purposes was consented, its purpose, whether the contact unsubscribed, the origin of the record, and how and when it was verified.

#### Scenario: Unsubscribed organization
- **WHEN** an organization has `unsubscribed_at` set
- **THEN** it SHALL be excluded from any "contactable" selection.

### Requirement: Organization provisioning for an entity
The system SHALL provide an idempotent operation that creates the primary organization of an entity, links it, and optionally adds an `owner` member.

#### Scenario: Solo artist
- **WHEN** the operation runs for an interprete with no organization
- **THEN** an organization named after the interprete SHALL be created and linked as primary.

#### Scenario: Repeated run
- **WHEN** the operation runs again for the same entity
- **THEN** it SHALL return the existing primary organization and create nothing.
