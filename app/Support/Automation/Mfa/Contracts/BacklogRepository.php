<?php

namespace App\Support\Automation\Mfa\Contracts;

use App\Support\Automation\Mfa\BacklogTask;

interface BacklogRepository
{
    /**
     * @return array<int, BacklogTask>
     */
    public function all(): array;

    /**
     * @param  array<string, mixed>  $context
     */
    public function claim(BacklogTask $task, array $context): array;

    /**
     * @param  array<string, mixed>  $context
     */
    public function markDone(BacklogTask $task, array $context): array;

    /**
     * @param  array<string, mixed>  $context
     */
    public function markNeedsReview(BacklogTask $task, array $context): array;

    /**
     * @param  array<string, mixed>  $context
     */
    public function markBlocked(BacklogTask $task, array $context): array;
}
