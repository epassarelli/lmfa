## ADDED Requirements

### Requirement: The orchestrator SHALL use Google Drive as the only active backlog source
The system SHALL select autonomous work only from the configured Google Sheets backlog for MFA and SHALL NOT use `project/backlog.json` as an active source of task priority or readiness.

#### Scenario: Selection ignores legacy backlog.json
- **WHEN** the orchestrator evaluates pending work
- **THEN** it reads candidate tasks from the configured Google Sheets backlog
- **AND** it does not derive executable priority from `project/backlog.json`

#### Scenario: Missing Drive mapping blocks the run
- **WHEN** the configured sheet, header mapping, or required backlog columns are unavailable
- **THEN** the orchestrator stops before claiming any task
- **AND** it records a structured blocking reason

### Requirement: The orchestrator SHALL select only executable autonomous tasks
The system SHALL consider only tasks with an autonomous-compatible role, no active blockers, resolved dependencies, and a pending-ready state, ordered by priority and deterministic tie-breakers.

#### Scenario: Autonomous task is eligible
- **WHEN** a task is pending, unblocked, dependency-complete, and marked `IA_AUTONOMA` or `IA_CON_VALIDACION`
- **THEN** the task can be selected for execution

#### Scenario: Blocked or unresolved task is skipped
- **WHEN** a task has active blockers or unresolved dependencies
- **THEN** the task is excluded from selection
- **AND** the exclusion reason is recorded

#### Scenario: Priority tie is deterministic
- **WHEN** multiple tasks are eligible with the same priority
- **THEN** the orchestrator resolves the tie using configured deterministic fields such as due date and row order

### Requirement: The orchestrator SHALL enforce one WIP per repository and safe task claiming
The system SHALL prevent concurrent work on the same repository and SHALL claim a remote task only if it remains eligible at write time.

#### Scenario: Local repository lock prevents a second run
- **WHEN** another active run already holds the repository lock
- **THEN** the new run stops before selecting or claiming a task

#### Scenario: Remote task claim writes ownership metadata
- **WHEN** the orchestrator claims a task successfully
- **THEN** it updates the backlog row to `En curso`
- **AND** it records agent identity and claim timestamp

#### Scenario: Lost claim race aborts execution
- **WHEN** the task changes remotely between selection and claim
- **THEN** the orchestrator aborts execution for that task
- **AND** it does not continue as if the claim had succeeded

### Requirement: The orchestrator SHALL support dry-run without permanent mutations
The system SHALL support a `dry-run` mode that exercises the circuit, selection and validation logic without persisting code changes or final backlog state transitions.

#### Scenario: Dry-run simulates selection and claim
- **WHEN** the orchestrator runs in `dry-run`
- **THEN** it reports the task it would choose, the context it would load and the validations it would run
- **AND** it does not permanently claim or complete the task in Drive

#### Scenario: Dry-run does not edit application code
- **WHEN** the orchestrator runs in `dry-run`
- **THEN** it does not apply repository code changes as part of execution

### Requirement: The orchestrator SHALL validate and review work before closing a task
The system SHALL execute configured validations and SHALL obtain an independent review result based on the produced diff and the task done criteria before updating the final backlog state.

#### Scenario: Successful task closes with evidence
- **WHEN** implementation, validations and independent review all succeed
- **THEN** the orchestrator updates the task to `Hecha`
- **AND** it records evidence of validations and review

#### Scenario: Human validation is still required
- **WHEN** the independent review determines that human confirmation is needed
- **THEN** the orchestrator updates the task to `En revisión`
- **AND** it records the exact pending validation

#### Scenario: Validation failure blocks closure
- **WHEN** required validations fail or the review finds blocking issues
- **THEN** the orchestrator updates the task to `Bloqueada`
- **AND** it records the cause and the next required action

### Requirement: The orchestrator SHALL honor operational limits and human-stop conditions
The system SHALL stop automatically when there are no executable tasks, when the configured time or task limit is reached, or when a required action needs explicit human authorization.

#### Scenario: No executable tasks ends the run
- **WHEN** the backlog has no eligible tasks
- **THEN** the orchestrator exits cleanly with a structured summary

#### Scenario: Human authorization stops autonomous continuation
- **WHEN** the current task requires deployment, production changes, destructive operations, secrets handling, or strategic decisions
- **THEN** the orchestrator stops before taking that action
- **AND** it records the authorization need

#### Scenario: Max task count ends the run
- **WHEN** the run reaches the configured maximum number of tasks
- **THEN** the orchestrator stops instead of claiming another task

### Requirement: The orchestrator SHALL keep structured local execution records
The system SHALL write structured logs for selection, claiming, execution, validation, review, final status, and errors for each run.

#### Scenario: Each run generates a structured record
- **WHEN** a run starts and finishes
- **THEN** the orchestrator writes a structured record containing timestamps, task identifiers, decisions, validations and outcome

#### Scenario: Error path remains auditable
- **WHEN** an exception or handled failure occurs
- **THEN** the orchestrator stores the stage, error summary and task context in the local run log

### Requirement: The project SHALL document codex exec usage and keep the scheduler inactive by default
The system SHALL include operator documentation for running the orchestrator through `codex exec` and SHALL provide a Windows Task Scheduler installation script that does not activate scheduling automatically.

#### Scenario: Manual execution instructions are available
- **WHEN** an operator reviews the orchestrator documentation
- **THEN** the documentation explains how to launch `dry-run` and real execution via `codex exec`

#### Scenario: Scheduler script does not activate automatically
- **WHEN** the installation script is generated
- **THEN** it prepares the Windows Task Scheduler registration command or task definition
- **AND** it does not enable the schedule without explicit human action

### Requirement: backlog.json SHALL remain present but explicitly legacy
The project SHALL preserve `project/backlog.json` in the repository while clearly marking it as legacy and not the active backlog.

#### Scenario: Legacy status is documented
- **WHEN** a contributor reads the backlog-related project documentation
- **THEN** they can see that `project/backlog.json` is legacy
- **AND** that Google Drive is the active backlog source
