<?php

namespace App\Support\Automation\Mfa;

class BuiltinEvidenceTaskExecutor
{
    /**
     * @param  array<string, mixed>  $projectConfig
     */
    public function execute(BacklogTask $task, array $projectConfig, bool $dryRun): TaskExecutionResult
    {
        $taskConfig = $projectConfig['execution']['builtin_evidence_tasks'][$task->id()] ?? null;

        if ($taskConfig === null) {
            return TaskExecutionResult::blocked(
                'No existe un handler local seguro para esta tarea y el bridge live depende de codex exec.'
            );
        }

        $missingFiles = [];
        $touchedFiles = [];

        foreach ($taskConfig['required_files'] ?? [] as $relativePath) {
            $absolutePath = base_path($relativePath);
            if (! is_file($absolutePath)) {
                $missingFiles[] = $relativePath;

                continue;
            }

            $touchedFiles[] = $relativePath;
        }

        if ($missingFiles !== []) {
            return TaskExecutionResult::blocked(
                'Faltan evidencias locales requeridas para cerrar la tarea.',
                array_map(fn (string $file) => 'Falta '.$file, $missingFiles),
                $touchedFiles
            );
        }

        $summary = (string) ($taskConfig['summary'] ?? 'Tarea validada localmente por evidencia existente.');
        $evidence = $taskConfig['evidence'] ?? [];

        if ($dryRun) {
            array_unshift($evidence, 'Dry-run: no se mutó el repositorio ni el backlog remoto.');
        }

        return TaskExecutionResult::completed($summary, $evidence, $touchedFiles);
    }
}
