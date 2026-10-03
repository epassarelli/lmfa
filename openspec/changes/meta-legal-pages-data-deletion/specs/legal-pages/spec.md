## ADDED Requirements

### Requirement: Public legal pages SHALL be available on stable routes
The system SHALL expose public pages for privacy policy, terms of service and data deletion instructions at stable, unauthenticated frontend routes that render with the existing public portal layout and return HTTP `200`.

#### Scenario: Privacy page is publicly accessible
- **WHEN** a visitor requests `GET /privacidad`
- **THEN** the system returns HTTP `200` without requiring authentication

#### Scenario: Terms page is publicly accessible
- **WHEN** a visitor requests `GET /condiciones`
- **THEN** the system returns HTTP `200` without requiring authentication

#### Scenario: Data deletion instructions page is publicly accessible
- **WHEN** a visitor requests `GET /eliminacion-de-datos`
- **THEN** the system returns HTTP `200` without requiring authentication

#### Scenario: Historical deleteuserdata page remains available
- **WHEN** a visitor requests `GET /deleteuserdata`
- **THEN** the system returns HTTP `200` with instructions equivalent to the data deletion page

### Requirement: Legal pages SHALL include canonical metadata and maintainable content
The system SHALL render each legal page with its own HTML title, meta description and canonical URL on the `.com` domain, and SHALL keep the content in separate maintainable templates without relying on JavaScript for the main text.

#### Scenario: Privacy page emits canonical metadata
- **WHEN** a visitor opens `/privacidad`
- **THEN** the rendered HTML includes a title, meta description and canonical URL pointing to `https://mifolkloreargentino.com/privacidad`

#### Scenario: Terms page emits canonical metadata
- **WHEN** a visitor opens `/condiciones`
- **THEN** the rendered HTML includes a title, meta description and canonical URL pointing to `https://mifolkloreargentino.com/condiciones`

#### Scenario: Delete instructions page emits canonical metadata
- **WHEN** a visitor opens `/eliminacion-de-datos`
- **THEN** the rendered HTML includes a title, meta description and canonical URL pointing to `https://mifolkloreargentino.com/eliminacion-de-datos`

### Requirement: Privacy policy SHALL describe only verified data practices
The privacy policy SHALL explain in clear Spanish the site owner, Facebook login data that may be received, processing purposes, cookies/analytics actually used, relevant third parties actually integrated, user rights, contact channel, deletion procedure and last update date, without asserting unverified practices.

#### Scenario: Privacy policy links to deletion process
- **WHEN** a visitor reads `/privacidad`
- **THEN** the page includes a visible link to `/eliminacion-de-datos`

#### Scenario: Privacy policy includes contact and update date
- **WHEN** a visitor reads `/privacidad`
- **THEN** the page shows the valid project contact email and a last updated date

### Requirement: Terms page SHALL describe usage and third-party authentication rules
The terms page SHALL describe in clear Spanish the portal identity and purpose, acceptance of terms, permitted use, accounts and third-party authentication, intellectual property, editorial and user-contributed content handling where applicable, links to third parties, reasonable limitation of liability, change management and contact details.

#### Scenario: Terms page describes authentication through third parties
- **WHEN** a visitor reads `/condiciones`
- **THEN** the page explains that account access may depend on third-party authentication providers actually used by the portal

### Requirement: Data deletion instructions SHALL present both deletion paths and editorial preservation rules
The data deletion instructions page SHALL explain both the Facebook-side deletion path and the direct request-by-email path, including the minimum identification data requested, estimated processing scope, and the rule that editorial content may be preserved while the account association is removed or anonymized.

#### Scenario: Instructions explain deletion from Facebook settings
- **WHEN** a visitor reads `/eliminacion-de-datos`
- **THEN** the page lists the Facebook settings path needed to remove Mi Folklore Argentino from Apps and Websites

#### Scenario: Instructions explain direct request by email
- **WHEN** a visitor reads `/eliminacion-de-datos`
- **THEN** the page explains which minimum information must be sent to the portal contact email
