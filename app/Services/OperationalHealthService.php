<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class OperationalHealthService
{
    private const SCHEDULER_HEARTBEAT_KEY = 'operations:scheduler_heartbeat';

    public function publicStatus(): array
    {
        $database = $this->databaseStatus();

        return [
            'status' => $database['status'] === 'ok' ? 'ok' : 'degraded',
            'checks' => ['database' => $database['status']],
        ];
    }

    public function diagnosis(): array
    {
        $checks = [
            'database' => $this->databaseStatus(),
            'cache' => $this->cacheStatus(),
            'scheduler' => $this->schedulerStatus(),
            'queue' => $this->queueStatus(),
        ];

        return [
            'status' => collect($checks)->contains(fn (array $check) => $check['status'] === 'degraded') ? 'degraded' : 'ok',
            'checks' => $checks,
        ];
    }

    public function recordSchedulerHeartbeat(): void
    {
        Cache::put(self::SCHEDULER_HEARTBEAT_KEY, now()->toIso8601String(), now()->addDay());
    }

    private function databaseStatus(): array
    {
        try {
            DB::select('select 1');

            return ['status' => 'ok'];
        } catch (Throwable) {
            return ['status' => 'degraded'];
        }
    }

    private function cacheStatus(): array
    {
        try {
            $key = 'operations:health_probe';
            Cache::put($key, 'ok', now()->addMinute());

            return ['status' => Cache::get($key) === 'ok' ? 'ok' : 'degraded'];
        } catch (Throwable) {
            return ['status' => 'degraded'];
        }
    }

    private function schedulerStatus(): array
    {
        try {
            $heartbeat = Cache::get(self::SCHEDULER_HEARTBEAT_KEY);

            if (! $heartbeat) {
                return ['status' => 'degraded', 'age_seconds' => null];
            }

            $age = now()->diffInSeconds($heartbeat);

            return ['status' => $age <= config('operations.health.scheduler_max_age_seconds') ? 'ok' : 'degraded', 'age_seconds' => $age];
        } catch (Throwable) {
            return ['status' => 'degraded', 'age_seconds' => null];
        }
    }

    private function queueStatus(): array
    {
        $connection = config('queue.default');

        return [
            'status' => $connection === 'sync' ? 'degraded' : 'ok',
            'connection' => $connection,
        ];
    }
}
