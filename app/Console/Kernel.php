<?php

namespace App\Console;

use App\Services\OperationalHealthService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        app(OperationalHealthService::class)->recordSchedulerHeartbeat();

        // Newsletter (spec newsletter-editorial-control): el lunes se arma el BORRADOR (no envía nada) y cada 15
        // minutos se despachan únicamente las ediciones APROBADAS por un administrador cuya hora programada llegó.
        $schedule->command('newsletter:build-draft')->weeklyOn(1, '07:00')->withoutOverlapping();
        $schedule->command('newsletter:dispatch-scheduled')->everyFifteenMinutes()->withoutOverlapping();

        // PC-09: Event reminders — runs daily at 08:00
        $schedule->job(new \App\Jobs\Publication\EventReminderJob())->dailyAt('08:00');

        // Process queued publication jobs (if not using an external worker like Supervisor)
        $schedule->command('queue:work --stop-when-empty --tries=3')->everyFiveMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
