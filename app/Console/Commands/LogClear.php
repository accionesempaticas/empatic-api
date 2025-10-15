<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class LogClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear {--keep=30 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old log files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting log cleanup...');

        $keepDays = $this->option('keep');
        $cutoffDate = Carbon::now()->subDays($keepDays);

        try {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/laravel-*.log');

            $deletedCount = 0;

            foreach ($files as $file) {
                $fileDate = Carbon::createFromTimestamp(filemtime($file));

                if ($fileDate->lt($cutoffDate)) {
                    if (unlink($file)) {
                        $this->info("🗑️  Deleted: " . basename($file));
                        $deletedCount++;
                    }
                }
            }

            if ($deletedCount > 0) {
                $this->info("✅ Cleaned up {$deletedCount} log file(s) older than {$keepDays} days");
            } else {
                $this->info("✅ No old log files to clean up");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Log cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
}