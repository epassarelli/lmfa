<?php

namespace App\Console\Commands;

use App\Support\Automation\Mfa\BacklogTaskSelector;
use App\Support\Automation\Mfa\BuiltinEvidenceTaskExecutor;
use App\Support\Automation\Mfa\IndependentReviewInvoker;
use App\Support\Automation\Mfa\MfaAutonomousOrchestrator;
use App\Support\Automation\Mfa\Repositories\BacklogRepositoryFactory;
use App\Support\Automation\Mfa\ValidationRunner;
use Illuminate\Console\Command;

class RunMfaAutonomousOrchestrator extends Command
{
    protected $signature = 'mfa:orchestrate-backlog
        {--project=mfa : Proyecto definido en config/mfa_orchestrator.php}
        {--driver= : fixture o codex_exec}
        {--dry-run : Ejecuta el circuito sin mutaciones permanentes}';

    protected $description = 'Orquesta tareas autónomas de MFA usando Google Drive como backlog activo o un snapshot local.';

    public function handle(): int
    {
        $projectKey = (string) $this->option('project');
        $config = config('mfa_orchestrator.projects.'.$projectKey);

        if (! is_array($config)) {
            $this->error('Proyecto no configurado: '.$projectKey);

            return self::FAILURE;
        }

        $orchestrator = new MfaAutonomousOrchestrator(
            new BacklogRepositoryFactory(),
            new BacklogTaskSelector(),
            new BuiltinEvidenceTaskExecutor(),
            new ValidationRunner(),
            new IndependentReviewInvoker(storage_path('app/automation/orchestrator/reports')),
        );

        $result = $orchestrator->run(
            $projectKey,
            $config,
            (bool) $this->option('dry-run'),
            $this->option('driver') ? (string) $this->option('driver') : null
        );

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return in_array($result['status'] ?? '', ['completed', 'needs_human_validation'], true)
            ? self::SUCCESS
            : self::FAILURE;
    }
}
