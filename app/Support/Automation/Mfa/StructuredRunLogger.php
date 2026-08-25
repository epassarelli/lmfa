<?php

namespace App\Support\Automation\Mfa;

class StructuredRunLogger
{
    protected string $runId;

    protected string $logFile;

    public function __construct(
        protected string $logDirectory,
    ) {
        $this->runId = now()->format('Ymd_His').'_'.bin2hex(random_bytes(4));

        if (! is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0777, true);
        }

        $this->logFile = rtrim($this->logDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->runId.'.jsonl';
    }

    public function runId(): string
    {
        return $this->runId;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function write(string $stage, array $payload): void
    {
        $record = [
            'timestamp' => now()->toIso8601String(),
            'run_id' => $this->runId,
            'stage' => $stage,
            'payload' => $payload,
        ];

        file_put_contents(
            $this->logFile,
            json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL,
            FILE_APPEND
        );
    }

    public function path(): string
    {
        return $this->logFile;
    }
}
