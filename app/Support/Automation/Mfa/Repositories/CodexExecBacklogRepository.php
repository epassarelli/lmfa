<?php

namespace App\Support\Automation\Mfa\Repositories;

use App\Support\Automation\Mfa\BacklogTask;
use App\Support\Automation\Mfa\Contracts\BacklogRepository;
use RuntimeException;
use Symfony\Component\Process\Process;

class CodexExecBacklogRepository implements BacklogRepository
{
    public function __construct(
        protected string $command,
    ) {
    }

    public function all(): array
    {
        $this->assertCodexExecAvailable();

        throw new RuntimeException('El driver codex_exec todavía no expone lectura local de Drive sin el bridge interactivo de Codex.');
    }

    public function claim(BacklogTask $task, array $context): array
    {
        $this->assertCodexExecAvailable();

        throw new RuntimeException('El driver codex_exec todavía no expone escrituras locales de Drive sin el bridge interactivo de Codex.');
    }

    public function markDone(BacklogTask $task, array $context): array
    {
        $this->assertCodexExecAvailable();

        throw new RuntimeException('El driver codex_exec todavía no expone escrituras locales de Drive sin el bridge interactivo de Codex.');
    }

    public function markNeedsReview(BacklogTask $task, array $context): array
    {
        $this->assertCodexExecAvailable();

        throw new RuntimeException('El driver codex_exec todavía no expone escrituras locales de Drive sin el bridge interactivo de Codex.');
    }

    public function markBlocked(BacklogTask $task, array $context): array
    {
        $this->assertCodexExecAvailable();

        throw new RuntimeException('El driver codex_exec todavía no expone escrituras locales de Drive sin el bridge interactivo de Codex.');
    }

    public function assertCodexExecAvailable(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new RuntimeException('codex exec debe ejecutarse en el host Windows con la app Codex instalada; no corresponde invocarlo desde contenedor Linux.');
        }

        $process = Process::fromShellCommandline($this->command.' --help');
        $process->setTimeout(15);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('codex exec no está disponible en este host: '.trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }
}
