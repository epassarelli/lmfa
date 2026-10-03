## ADDED Requirements

### Requirement: Unique contact with multiple channels
The system SHALL store each real-world contact once, with zero or more contact channels. Each channel MUST have a type, the original value and a normalized value, and the pair (channel, normalized value) MUST be unique across the directory.

#### Scenario: Same Instagram in different formats
- **WHEN** one source stores `https://www.instagram.com/losmanserosoficial/` and another stores `@losmanserosoficial`
- **THEN** both SHALL resolve to the same normalized handle and the same contact.

#### Scenario: Value that is not a valid channel
- **WHEN** an imported facebook value is a person's name instead of a URL or handle
- **THEN** the channel SHALL be kept with status `needs_review` and not be offered as a contact action.

### Requirement: Links to portal entities
The system SHALL allow a contact to be linked to any number of portal entities (Interprete, Festival, Event, PeniaProfile, RadioSignal, Organization, Venue) with a role, and an entity to have several contacts.

#### Scenario: Production company for several artists
- **WHEN** the same email belongs to two artists' records
- **THEN** the directory SHALL hold one contact linked to both artists.

### Requirement: Idempotent import from existing records
The system SHALL provide an import command that reads existing records without modifying them, normalizes and deduplicates channels, creates links and reports results. Running it repeatedly MUST NOT create duplicates nor overwrite manually edited channels. A dry-run mode MUST report without writing.

#### Scenario: Re-running the import
- **WHEN** the import runs twice over unchanged data
- **THEN** the second run SHALL create zero contacts, channels and links.

#### Scenario: Possibly truncated legacy value
- **WHEN** a legacy email has exactly 30 characters or a social value exactly 50
- **THEN** the channel SHALL be imported with status `needs_review`.

### Requirement: Backoffice directory and profile
The system SHALL provide an authenticated, permission-protected listing with server-side pagination, search and filters by segment, province, available channel, channel status, never contacted and last contact date; and a contact profile showing channels, links, interactions and notes.

#### Scenario: Find festivals never contacted
- **WHEN** an editor filters segment `festival` and `never_contacted`
- **THEN** the listing SHALL return only festival contacts without interactions.

### Requirement: Assisted contact actions with history
The system SHALL offer one-click actions for valid channels (WhatsApp link with prefilled message, open social profile and copy message, mailto) and SHALL record an outbound interaction and update the last-contacted date when an action is used. Contacts marked `do_not_contact` or opted out MUST NOT show actions.

#### Scenario: Contact via WhatsApp
- **WHEN** an editor uses the WhatsApp action on a valid phone
- **THEN** the system SHALL open `wa.me` with the normalized number and message and log the interaction.

### Requirement: Internal-only data
The directory SHALL never expose CRM data in public pages, sitemaps, APIs without authentication or structured data.

#### Scenario: Public artist page
- **WHEN** a visitor opens an artist profile
- **THEN** no data originating only from the CRM SHALL appear.
