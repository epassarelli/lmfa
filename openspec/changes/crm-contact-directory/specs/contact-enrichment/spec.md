## ADDED Requirements

### Requirement: Candidate queue with mandatory source
The system SHALL store proposed contact channels as candidates with channel, value, normalized value, detector, source URL and target contact or entity. Candidates without a source URL MUST be rejected at creation.

#### Scenario: AI proposes an Instagram for an artist
- **WHEN** the enrichment job finds an Instagram handle on the artist's official website
- **THEN** a `pending` candidate SHALL be created with that website as source and linked to the artist.

### Requirement: Human approval before use
Candidates SHALL NOT be usable for contact actions until approved by an authorized user. Approval MUST create or update a valid channel recording verifier and verification date; rejection MUST prevent the same normalized value from being proposed again for the same target.

#### Scenario: Approve a candidate
- **WHEN** an editor approves a pending email candidate
- **THEN** the email SHALL become a valid channel of the contact with `verified_at` and `verified_by` set.

### Requirement: Text extraction from existing content
The system SHALL provide a command that extracts emails, social handles and phone numbers from the body of events and festivals into the candidate queue, linked to the source entity, without modifying the content.

#### Scenario: Event body with Instagram handle
- **WHEN** an event body mentions an organizer's Instagram
- **THEN** a `text_extraction` candidate SHALL be created linked to that event.
