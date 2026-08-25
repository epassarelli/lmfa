<?php

namespace App\Support\Automation\Mfa;

class BacklogSelectionResult
{
    /**
     * @param  array<int, array<string, mixed>>  $skipped
     */
    public function __construct(
        public readonly ?BacklogTask $selectedTask,
        public readonly array $skipped,
    ) {
    }
}
