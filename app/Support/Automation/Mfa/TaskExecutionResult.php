<?php

namespace App\Support\Automation\Mfa;

class TaskExecutionResult
{
    /**
     * @param  array<int, string>  $evidence
     * @param  array<int, string>  $touchedFiles
     */
    public function __construct(
        public readonly string $status,
        public readonly string $summary,
        public readonly array $evidence = [],
        public readonly array $touchedFiles = [],
    ) {
    }

    public static function completed(string $summary, array $evidence = [], array $touchedFiles = []): self
    {
        return new self('completed', $summary, $evidence, $touchedFiles);
    }

    public static function blocked(string $summary, array $evidence = [], array $touchedFiles = []): self
    {
        return new self('blocked', $summary, $evidence, $touchedFiles);
    }
}
