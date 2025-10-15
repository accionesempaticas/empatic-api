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
        // Local database backup every 12 hours
        $schedule->command('db:backup --keep=14')
                 ->twiceDaily(6, 18) // 6:00 AM and 6:00 PM
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->emailOutputOnFailure(config('mail.admin_email', 'admin@example.com'));

        // Cloud backup daily (more persistent)
        $schedule->command('db:cloud-backup --provider=local')
                 ->daily()
                 ->at('03:00')
                 ->withoutOverlapping()
                 ->onOneServer();

        // Optional: Cloud backup to external provider (uncomment and configure)
        // $schedule->command('db:cloud-backup --provider=s3')
        //          ->daily()
        //          ->at('04:00')
        //          ->withoutOverlapping()
        //          ->onOneServer();

        // Clean up old logs weekly
        $schedule->command('log:clear --keep=30')
                 ->weekly()
                 ->sundays()
                 ->at('02:00');
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