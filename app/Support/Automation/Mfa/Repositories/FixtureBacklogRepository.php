<?php

namespace App\Support\Automation\Mfa\Repositories;

use App\Support\Automation\Mfa\BacklogTask;
use App\Support\Automation\Mfa\Contracts\BacklogRepository;

class FixtureBacklogRepository implements BacklogRepository
{
    public function __construct(
        protected string $fixturePath,
    ) {
    }

    public function all(): array
    {
        $payload = $this->readFixture();
        $header = $payload['header'] ?? [];
        $rows = $payload['rows'] ?? [];
        $tasks = [];

        foreach ($rows as $index => $rowValues) {
            $row = [];
            foreach ($header as $columnIndex => $columnName) {
                $row[$columnName] = (string) ($rowValues[$columnIndex] ?? '');
            }

            $tasks[] = new BacklogTask(($row['__row_number'] ?? $index + 2), $row);
        }

        return $tasks;
    }

    public function claim(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            'Estado delegacion' => 'En curso',
            'Notas cierre' => $this->appendNote($task->notes(), $context['note'] ?? ''),
        ]);
    }

    public function markDone(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            'Estado' => 'Hecha',
            'Estado delegacion' => 'Cerrada',
            'Notas cierre' => $this->appendNote($task->notes(), $context['note'] ?? ''),
        ]);
    }

    public function markNeedsReview(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            'Estado' => 'En revisión',
            'Estado delegacion' => 'En revision',
            'Notas cierre' => $this->appendNote($task->notes(), $context['note'] ?? ''),
        ]);
    }

    public function markBlocked(BacklogTask $task, array $context): array
    {
        return $this->mutateTask($task, [
            'Estado' => 'Bloqueada',
            'Estado delegacion' => 'Bloqueada',
            'Notas cierre' => $this->appendNote($task->notes(), $context['note'] ?? ''),
        ]);
    }

    protected function mutateTask(BacklogTask $task, array $updates): array
    {
        $payload = $this->readFixture();
        $header = $payload['header'] ?? [];
        $rows = $payload['rows'] ?? [];

        foreach ($rows as $rowIndex => $rowValues) {
            $rowNumber = $rowIndex + 2;
            if ($rowNumber !== $task->rowNumber && (string) ($rowValues[0] ?? '') !== $task->id()) {
                continue;
            }

            foreach ($updates as $columnName => $value) {
                $columnIndex = array_search($columnName, $header, true);
                if ($columnIndex === false) {
                    continue;
                }

                $rowValues[$columnIndex] = $value;
            }

            $rows[$rowIndex] = $rowValues;
            $payload['rows'] = $rows;

            file_put_contents($this->fixturePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return [
                'driver' => 'fixture',
                'task_id' => $task->id(),
                'row_number' => $task->rowNumber,
                'updates' => $updates,
            ];
        }

        return [
            'driver' => 'fixture',
            'task_id' => $task->id(),
            'error' => 'task_not_found_in_fixture',
        ];
    }

    protected function readFixture(): array
    {
        $contents = file_get_contents($this->fixturePath);

        return json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function appendNote(string $current, string $note): string
    {
        $note = trim($note);
        if ($note === '') {
            return $current;
        }

        return trim($current."\n".$note);
    }
}
