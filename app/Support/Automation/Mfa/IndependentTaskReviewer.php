<?php

namespace App\Support\Automation\Mfa;

class IndependentTaskReviewer
{
    /**
     * @param  array<int, array<string, mixed>>  $validationResults
     */
    public function review(BacklogTask $task, TaskExecutionResult $executionResult, array $validationResults): array
    {
        foreach ($validationResults as $validationResult) {
            if (($validationResult['ok'] ?? false) !== true) {
                return [
                    'status' => 'blocked',
                    'summary' => 'La validación configurada falló.',
                ];
            }
        }

        if ($executionResult->status !== 'completed') {
            return [
                'status' => 'blocked',
                'summary' => $executionResult->summary,
            ];
        }

        if ($task->autonomyLabel() === 'IA_CON_VALIDACION') {
            return [
                'status' => 'needs_human_validation',
                'summary' => 'La tarea es IA_CON_VALIDACION y requiere revisión humana final.',
            ];
        }

        return [
            'status' => 'approved',
            'summary' => 'Revisión independiente aprobada.',
        ];
    }
}
