<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Newsletter - weekly (Monday 10:00)
        $schedule->command('newsletter:send-weekly')->weeklyOn(1, '10:00');

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
