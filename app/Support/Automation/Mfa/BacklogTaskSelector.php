<?php

namespace App\Support\Automation\Mfa;

class BacklogTaskSelector
{
    /**
     * @param  array<int, BacklogTask>  $tasks
     * @param  array<string, mixed>  $eligibility
     */
    public function select(array $tasks, array $eligibility): BacklogSelectionResult
    {
        $pendingStates = array_map('mb_strtolower', $eligibility['pending_states'] ?? []);
        $blockedStates = array_map('mb_strtolower', $eligibility['blocked_states'] ?? []);
        $allowedProjectFragments = $eligibility['allowed_project_fragments'] ?? [];
        $priorityOrder = $eligibility['priority_order'] ?? [];
        $doneById = [];

        foreach ($tasks as $task) {
            $doneById[$task->id()] = $task->isDone();
        }

        $eligible = [];
        $skipped = [];

        foreach ($tasks as $task) {
            $reasons = [];
            $project = $task->project();

            if (! $this->matchesProject($project, $allowedProjectFragments)) {
                $reasons[] = 'outside_project_scope';
            }

            if (! in_array(mb_strtolower($task->status()), $pendingStates, true)) {
                $reasons[] = 'status_not_pending';
            }

            if (in_array(mb_strtolower($task->status()), $blockedStates, true)) {
                $reasons[] = 'status_blocked';
            }

            if ($task->autonomyLabel() === null) {
                $reasons[] = 'missing_autonomy_label';
            }

            $unresolvedDependencies = [];
            foreach ($task->dependencyTaskIds() as $dependencyId) {
                if (($doneById[$dependencyId] ?? false) !== true) {
                    $unresolvedDependencies[] = $dependencyId;
                }
            }

            if ($unresolvedDependencies !== []) {
                $reasons[] = 'unresolved_dependencies:'.implode(',', $unresolvedDependencies);
            }

            if ($reasons === []) {
                $eligible[] = $task;

                continue;
            }

            $skipped[] = [
                'task' => $task->toArray(),
                'reasons' => $reasons,
            ];
        }

        usort($eligible, function (BacklogTask $left, BacklogTask $right) use ($priorityOrder) {
            $leftPriority = array_search($left->priority(), $priorityOrder, true);
            $rightPriority = array_search($right->priority(), $priorityOrder, true);

            $leftPriority = $leftPriority === false ? PHP_INT_MAX : $leftPriority;
            $rightPriority = $rightPriority === false ? PHP_INT_MAX : $rightPriority;

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            return $left->rowNumber <=> $right->rowNumber;
        });

        return new BacklogSelectionResult($eligible[0] ?? null, $skipped);
    }

    /**
     * @param  string[]  $allowedProjectFragments
     */
    protected function matchesProject(string $project, array $allowedProjectFragments): bool
    {
        if ($allowedProjectFragments === []) {
            return true;
        }

        foreach ($allowedProjectFragments as $fragment) {
            if (str_contains(mb_strtolower($project), mb_strtolower($fragment))) {
                return true;
            }
        }

        return false;
    }
}
