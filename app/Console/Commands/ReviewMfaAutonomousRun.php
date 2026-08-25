<?php

namespace App\Console\Commands;

use App\Support\Automation\Mfa\BacklogTask;
use App\Support\Automation\Mfa\IndependentTaskReviewer;
use App\Support\Automation\Mfa\TaskExecutionResult;
use Illuminate\Console\Command;

class ReviewMfaAutonomousRun extends Command
{
    protected $signature = 'mfa:review-run {--input= : Ruta a un JSON de review request}';

    protected $description = 'Ejecuta una revisión independiente de una corrida del orquestador MFA.';

    public function handle(): int
    {
        $inputPath = (string) $this->option('input');

        if ($inputPath === '' || ! is_file($inputPath)) {
            $this->error(json_encode([
                'status' => 'blocked',
                'summary' => 'Archivo de entrada de revisión inexistente.',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::FAILURE;
        }

        $payload = json_decode(file_get_contents($inputPath), true, 512, JSON_THROW_ON_ERROR);
        $taskPayload = $payload['task'] ?? [];
        $executionPayload = $payload['execution'] ?? [];

        $task = new BacklogTask(
            (int) ($taskPayload['row_number'] ?? 0),
            [
                'ID' => $taskPayload['id'] ?? '',
                'Proyecto' => $taskPayload['project'] ?? '',
                'Tarea' => $taskPayload['title'] ?? '',
                'Estado' => $taskPayload['status'] ?? '',
                'Prioridad sugerida' => $taskPayload['priority'] ?? '',
                'Dependencia / bloqueo' => $taskPayload['dependency_blocker'] ?? '',
                'Responsable bloqueo' => $taskPayload['blocker_owner'] ?? '',
                'Estado delegacion' => $taskPayload['delegation_state'] ?? '',
                'Notas cierre' => $taskPayload['notes'] ?? '',
            ],
        );

        $executionResult = new TaskExecutionResult(
            $executionPayload['status'] ?? 'blocked',
            $executionPayload['summary'] ?? '',
            $executionPayload['evidence'] ?? [],
            $executionPayload['touched_files'] ?? [],
        );

        $review = (new IndependentTaskReviewer())->review(
            $task,
            $executionResult,
            $payload['validation'] ?? [],
        );

        $this->line(json_encode($review, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return in_array($review['status'] ?? '', ['approved', 'needs_human_validation'], true)
            ? self::SUCCESS
            : self::FAILURE;
    }
}
