<?php

namespace App\Support\Automation\Mfa;

use Symfony\Component\Process\Process;

class IndependentReviewInvoker
{
    public function __construct(
        protected string $reportDirectory,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $validationResults
     * @return array<string, mixed>
     */
    public function invoke(BacklogTask $task, TaskExecutionResult $executionResult, array $validationResults): array
    {
        if (! is_dir($this->reportDirectory)) {
            mkdir($this->reportDirectory, 0777, true);
        }

        $requestPath = rtrim($this->reportDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'review_request_'.bin2hex(random_bytes(4)).'.json';
        $payload = [
            'task' => $task->toArray(),
            'execution' => [
                'status' => $executionResult->status,
                'summary' => $executionResult->summary,
                'evidence' => $executionResult->evidence,
                'touched_files' => $executionResult->touchedFiles,
            ],
            'validation' => $validationResults,
        ];

        file_put_contents($requestPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $command = sprintf(
            '%s artisan mfa:review-run --input=%s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($requestPath)
        );

        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(120);
        $process->run();

        return [
            'independent_process' => true,
            'command' => $command,
            'request_path' => $requestPath,
            'ok' => $process->isSuccessful(),
            'output' => trim($process->getOutput()),
            'error_output' => trim($process->getErrorOutput()),
        ];
    }
}
