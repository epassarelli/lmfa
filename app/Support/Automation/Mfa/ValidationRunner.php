<?php

namespace App\Support\Automation\Mfa;

use Symfony\Component\Process\Process;

class ValidationRunner
{
    /**
     * @param  array<int, string>  $commands
     * @return array<int, array<string, mixed>>
     */
    public function run(array $commands, bool $dryRun): array
    {
        $results = [];

        foreach ($commands as $command) {
            if ($dryRun) {
                $results[] = [
                    'command' => $command,
                    'ok' => true,
                    'dry_run' => true,
                    'output' => 'Dry-run: comando no ejecutado.',
                ];

                continue;
            }

            $process = Process::fromShellCommandline($command, base_path());
            $process->setTimeout(600);
            $process->run();

            $results[] = [
                'command' => $command,
                'ok' => $process->isSuccessful(),
                'output' => trim($process->getOutput()),
                'error_output' => trim($process->getErrorOutput()),
            ];
        }

        return $results;
    }
}
