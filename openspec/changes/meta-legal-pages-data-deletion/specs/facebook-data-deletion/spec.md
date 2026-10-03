## ADDED Requirements

### Requirement: Meta callback SHALL validate signed requests cryptographically
The system SHALL accept `POST /deleteuserdata` as a public callback for Meta/Facebook only when the request includes a valid `signed_request` whose payload decodes correctly, declares `HMAC-SHA256`, and matches a signature recalculated with `config('services.facebook.client_secret')`.

#### Scenario: Missing signed_request is rejected
- **WHEN** a client posts to `/deleteuserdata` without `signed_request`
- **THEN** the system returns an HTTP `4xx` JSON response and does not create a deletion request

#### Scenario: Invalid signature is rejected
- **WHEN** a client posts a `signed_request` whose signature does not match the configured Facebook app secret
- **THEN** the system returns an HTTP `4xx` JSON response and does not create a deletion request

#### Scenario: Unsupported algorithm is rejected
- **WHEN** a client posts a validly encoded payload whose `algorithm` is not `HMAC-SHA256`
- **THEN** the system returns an HTTP `4xx` JSON response and does not create a deletion request

#### Scenario: Valid signature is accepted
- **WHEN** a client posts a correctly signed Facebook payload with a `user_id`
- **THEN** the system accepts the callback and continues the deletion workflow

### Requirement: The callback SHALL create an auditable and idempotent deletion request
For each accepted Facebook callback, the system SHALL register a deletion request with a unique high-entropy confirmation code, provider identifier, status, timestamps and the minimum safe references needed to process the deletion, and SHALL make repeat processing of the same effective account state safe and non-destructive.

#### Scenario: Successful callback returns Meta response payload
- **WHEN** the system accepts a valid deletion callback
- **THEN** it returns JSON containing `url` and `confirmation_code`

#### Scenario: Meta response uses canonical domain
- **WHEN** the system returns the status URL for an accepted callback
- **THEN** the `url` value uses `https://mifolkloreargentino.com/deleteuserdata/status/{confirmationCode}`

#### Scenario: Unknown Facebook user still produces a valid request
- **WHEN** the signed payload contains a Facebook user identifier that does not map to any local account
- **THEN** the system records the request in a terminal safe state and still returns a valid `url` and `confirmation_code`

#### Scenario: Repeated deletion does not fail
- **WHEN** the same effective account is processed more than once
- **THEN** the system completes without duplicating destructive side effects or leaving inconsistent state

### Requirement: Data deletion SHALL remove or anonymize account-linked personal data without breaking editorial integrity
When a Facebook-linked local account is identified, the system SHALL revoke or remove stored Facebook credentials and associations, invalidate active sessions when appropriate, and remove or anonymize personal data no longer needed, while preserving editorial records that must remain public or relationally intact.

#### Scenario: Facebook association is removed from a matched local account
- **WHEN** a valid deletion request is processed for a mapped local account
- **THEN** the stored Facebook linkage and related credentials are removed or nulled according to the real schema

#### Scenario: Editorial records remain intact
- **WHEN** a valid deletion request is processed for a local account that authored or is related to editorial content
- **THEN** the related editorial records remain queryable and no required foreign key integrity is broken

### Requirement: Public status page SHALL expose only generic request information
The system SHALL expose `GET /deleteuserdata/status/{confirmationCode}` as a public page that reveals only the confirmation code, current status, request date, completion date if present, and a generic message, and SHALL return `404` for unknown codes.

#### Scenario: Valid confirmation code shows generic status
- **WHEN** a visitor requests `/deleteuserdata/status/{confirmationCode}` for an existing request
- **THEN** the page renders the confirmation code, status and dates without exposing personal identifiers or internal errors

#### Scenario: Unknown confirmation code returns 404
- **WHEN** a visitor requests `/deleteuserdata/status/{confirmationCode}` with a non-existent code
- **THEN** the system returns HTTP `404`

### Requirement: CSRF handling SHALL be narrowly scoped to the Meta callback
The system SHALL exempt only the `deleteuserdata` callback route from CSRF verification and SHALL keep CSRF protection enabled for unrelated web routes.

#### Scenario: External callback can post without CSRF token
- **WHEN** Meta posts a valid request to `/deleteuserdata`
- **THEN** the request is processed without requiring a CSRF token

#### Scenario: Unrelated protected route still requires CSRF
- **WHEN** a client posts to another web route that remains protected
- **THEN** the framework continues enforcing CSRF validation there
