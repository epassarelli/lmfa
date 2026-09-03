## ADDED Requirements

### Requirement: Radio programs with optional signal ownership
The system SHALL manage a program as an editorial entity that belongs to zero or one canonical signal. A program without a signal SHALL be treated as an independent streaming program and MUST have its own verified listening platform or channel.

#### Scenario: Publish an independent YouTube program
- **WHEN** an editor publishes a program with no signal, a verified YouTube channel and current sources
- **THEN** the public directory SHALL expose it as an independent streaming program without inventing a radio relationship.

### Requirement: Weekly programming slots
The system SHALL allow each program to define zero or more explicit weekly slots with day, start time, end time and timezone. A signal schedule MUST be composed only from explicit program slots.

#### Scenario: Show upcoming scheduled program
- **WHEN** a published radio has a program with a current weekly slot
- **THEN** its profile SHALL show the program and its next scheduled broadcast in the configured timezone.

#### Scenario: Show on-demand program without a schedule
- **WHEN** a published independent program has no weekly slots but has a verified channel
- **THEN** the program SHALL remain discoverable and be identified as on-demand or without a published schedule.

### Requirement: Programming discovery
The system SHALL provide a public program directory that includes radio-owned and independent programs, with filters by parent signal, weekday and platform. The system MUST NOT infer a program-radio relationship from title or platform text.

#### Scenario: Filter programs by weekday
- **WHEN** a visitor filters programming by a weekday
- **THEN** the system SHALL return only programs with an explicit slot on that weekday, preserving independent programs when they match.

### Requirement: Program editorial quality
The system SHALL require every published program to have identity, description, source references, responsible verification and current verification date. A radio-owned program MAY use an approved channel from its parent signal; an independent program MUST provide its own approved channel.

#### Scenario: Reject an unverifiable independent program
- **WHEN** an editor attempts to publish an independent program without a channel, source or current verification
- **THEN** the system SHALL reject publication and report the missing requirements.
