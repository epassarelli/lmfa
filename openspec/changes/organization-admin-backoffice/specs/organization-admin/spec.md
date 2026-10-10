## ADDED Requirements

### Requirement: Administrator-only access
The system SHALL restrict every organization administration route to users with the `administrador` role.

#### Scenario: Collaborator
- **WHEN** a user with role `colaborador` requests `admin/organizations`
- **THEN** the response SHALL be forbidden.

### Requirement: Organization listing
The system SHALL list organizations paginated server-side, searchable by name, slug or email, and filterable by type, status, verified flag and contactable state, showing counts of linked entities and active members.

#### Scenario: Filter by type
- **WHEN** the administrator filters by type `productora`
- **THEN** only organizations of that type SHALL be listed.

### Requirement: Organization create and edit
The system SHALL let the administrator create and edit an organization with validated type, name, unique slug, contact data, status, verification data, contact consent and unsubscribe. Organizations MUST NOT be physically deleted.

#### Scenario: Duplicate slug
- **WHEN** the administrator saves an organization with an existing slug
- **THEN** validation SHALL fail without saving.

#### Scenario: Deactivation revokes permissions
- **GIVEN** an editor of an organization linked to an interprete
- **WHEN** the administrator sets the organization to `inactive`
- **THEN** the editor SHALL no longer be able to manage that interprete.

### Requirement: Linked entities
The system SHALL let the administrator link and unlink portal entities (Interprete, Festival) through remote search, avoiding duplicates, and mark a link as primary.

#### Scenario: Link an interprete
- **WHEN** the administrator selects an interprete and confirms
- **THEN** the link SHALL exist and members with writer roles SHALL be able to manage that interprete.

#### Scenario: Duplicate link
- **WHEN** the same entity is linked twice
- **THEN** only one link SHALL exist.

#### Scenario: Unknown entity type
- **WHEN** a request names an entity type not registered in configuration
- **THEN** validation SHALL fail.

### Requirement: Members
The system SHALL let the administrator add a user as member with role (`owner`, `editor`, `member`) and status (`active`, `pending`, `inactive`), change both and remove the membership. A user MUST NOT be added twice to the same organization.

#### Scenario: Approve a pending member
- **GIVEN** a `pending` editor
- **WHEN** the administrator sets the status to `active`
- **THEN** the user SHALL be able to manage the linked entities.

#### Scenario: Duplicate member
- **WHEN** an existing member is added again
- **THEN** validation SHALL fail.

### Requirement: Lookups
The system SHALL expose administrator-only JSON lookups that return at most 20 entities or users matching a query.

#### Scenario: Short query
- **WHEN** the query has fewer than 2 characters
- **THEN** the lookup SHALL return an empty result.
