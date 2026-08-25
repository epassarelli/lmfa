<?php

namespace App\Support\Automation\Mfa;

use App\Support\Automation\Mfa\Repositories\BacklogRepositoryFactory;

class MfaAutonomousOrchestrator
{
    public function __construct(
        protected BacklogRepositoryFactory $repositoryFactory,
        protected BacklogTaskSelector $selector,
        protected BuiltinEvidenceTaskExecutor $executor,
        protected ValidationRunner $validationRunner,
        protected IndependentReviewInvoker $reviewInvoker,
    ) {
    }

    /**
     * @param  array<string, mixed>  $projectConfig
     * @return array<string, mixed>
     */
    public function run(string $projectKey, array $projectConfig, bool $dryRun = false, ?string $driver = null): array
    {
        $paths = $projectConfig['paths'] ?? [];
        $logger = new StructuredRunLogger(storage_path($paths['logs'] ?? 'app/automation/orchestrator/logs'));
        $lock = new LocalRepositoryLock(storage_path($paths['locks'] ?? 'app/automation/orchestrator/locks'));
        $repository = $this->repositoryFactory->make($projectConfig, $driver);

        $lockMetadata = [
            'project' => $projectKey,
            'run_id' => $logger->runId(),
            'created_at' => now()->toIso8601String(),
            'dry_run' => $dryRun,
        ];

        if (! $lock->acquire($projectKey, $lockMetadata)) {
            $logger->write('lock_failed', $lockMetadata);

            return [
                'status' => 'blocked',
                'reason' => 'repository_lock_active',
                'run_id' => $logger->runId(),
                'log_path' => $logger->path(),
            ];
        }

        try {
            $logger->write('start', [
                'project' => $projectKey,
                'driver' => $driver ?: ($projectConfig['execution']['default_driver'] ?? 'fixture'),
                'dry_run' => $dryRun,
            ]);

            $tasks = $repository->all();
            $selection = $this->selector->select($tasks, $projectConfig['eligibility'] ?? []);
            $logger->write('selection', [
                'selected' => $selection->selectedTask?->toArray(),
                'skipped' => $selection->skipped,
            ]);

            if ($selection->selectedTask === null) {
                return [
                    'status' => 'completed',
                    'reason' => 'no_executable_tasks',
                    'run_id' => $logger->runId(),
                    'log_path' => $logger->path(),
                ];
            }

            $task = $selection->selectedTask;
            $claimNote = sprintf(
                '%s — Reclamo automático %s por %s.',
                now()->format('Y-m-d H:i'),
                $dryRun ? '(dry-run)' : '',
                'Codex local'
            );

            $claimResult = $dryRun
                ? ['driver' => 'dry-run', 'task_id' => $task->id()]
                : $repository->claim($task, ['note' => $claimNote]);

            $logger->write('claim', $claimResult);

            $executionResult = $this->executor->execute($task, $projectConfig, $dryRun);
            $logger->write('execution', [
                'task' => $task->toArray(),
                'status' => $executionResult->status,
                'summary' => $executionResult->summary,
                'evidence' => $executionResult->evidence,
                'touched_files' => $executionResult->touchedFiles,
            ]);

            $validationResults = $this->validationRunner->run($projectConfig['validation']['commands'] ?? [], $dryRun);
            $logger->write('validation', $validationResults);

            $reviewInvocation = $this->reviewInvoker->invoke($task, $executionResult, $validationResults);
            $logger->write('review_invocation', $reviewInvocation);

            $review = json_decode((string) ($reviewInvocation['output'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
            $logger->write('review', $review);

            $finalNote = $this->buildFinalNote($task, $executionResult, $review, $dryRun, $logger->runId());

            $finalUpdate = ['driver' => 'dry-run', 'task_id' => $task->id()];
            if (! $dryRun) {
                $finalUpdate = match ($review['status']) {
                    'approved' => $repository->markDone($task, ['note' => $finalNote]),
                    'needs_human_validation' => $repository->markNeedsReview($task, ['note' => $finalNote]),
                    default => $repository->markBlocked($task, ['note' => $finalNote]),
                };
            }

            $logger->write('final_update', $finalUpdate);

            return [
                'status' => $review['status'] === 'approved' ? 'completed' : $review['status'],
                'task' => $task->toArray(),
                'execution' => [
                    'status' => $executionResult->status,
                    'summary' => $executionResult->summary,
                    'evidence' => $executionResult->evidence,
                    'touched_files' => $executionResult->touchedFiles,
                ],
                'review_invocation' => $reviewInvocation,
                'review' => $review,
                'validation' => $validationResults,
                'run_id' => $logger->runId(),
                'log_path' => $logger->path(),
            ];
        } catch (\Throwable $throwable) {
            $logger->write('error', [
                'message' => $throwable->getMessage(),
                'class' => $throwable::class,
            ]);

            return [
                'status' => 'blocked',
                'reason' => $throwable->getMessage(),
                'run_id' => $logger->runId(),
                'log_path' => $logger->path(),
            ];
        } finally {
            $lock->release();
        }
    }

    protected function buildFinalNote(
        BacklogTask $task,
        TaskExecutionResult $executionResult,
        array $review,
        bool $dryRun,
        string $runId
    ): string {
        $prefix = sprintf('%s — Run %s', now()->format('Y-m-d H:i'), $runId);

        $body = match ($review['status']) {
            'approved' => 'Hecha.',
            'needs_human_validation' => 'En revisión.',
            default => 'Bloqueada.',
        };

        $evidence = $executionResult->evidence === []
            ? ''
            : ' Evidencia: '.implode(' | ', $executionResult->evidence);

        $dryRunSuffix = $dryRun ? ' Dry-run: no se persistieron cambios remotos.' : '';

        return trim($prefix.' '.$body.' '.$executionResult->summary.'. '.$review['summary'].$evidence.$dryRunSuffix);
    }
}
