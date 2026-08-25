<?php

namespace App\Support\Automation\Mfa;

class LocalRepositoryLock
{
    protected ?string $lockPath = null;

    public function __construct(
        protected string $lockDirectory,
    ) {
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function acquire(string $projectKey, array $metadata): bool
    {
        if (! is_dir($this->lockDirectory)) {
            mkdir($this->lockDirectory, 0777, true);
        }

        $this->lockPath = rtrim($this->lockDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$projectKey.'.lock.json';

        if (is_file($this->lockPath)) {
            return false;
        }

        file_put_contents($this->lockPath, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return true;
    }

    public function release(): void
    {
        if ($this->lockPath !== null && is_file($this->lockPath)) {
            @unlink($this->lockPath);
        }
    }
}
