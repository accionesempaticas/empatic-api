<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=7 : Number of backups to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the SQLite database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting database backup...');

        try {
            // Get the database path
            $databasePath = database_path('database.sqlite');

            if (!file_exists($databasePath)) {
                $this->error('❌ Database file not found at: ' . $databasePath);
                return 1;
            }

            // Create backup filename with timestamp
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupFilename = "database_backup_{$timestamp}.sqlite";
            $backupPath = storage_path("app/backups/{$backupFilename}");

            // Ensure backup directory exists
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
                $this->info("📁 Created backup directory: {$backupDir}");
            }

            // Copy the database file
            if (copy($databasePath, $backupPath)) {
                $this->info("✅ Database backup created successfully:");
                $this->info("   📁 File: {$backupFilename}");
                $this->info("   📊 Size: " . $this->formatBytes(filesize($backupPath)));

                // Clean up old backups
                $this->cleanupOldBackups($this->option('keep'));

                // Log backup creation
                \Log::info('Database backup created', [
                    'filename' => $backupFilename,
                    'size' => filesize($backupPath),
                    'timestamp' => Carbon::now()
                ]);

                return 0;
            } else {
                $this->error('❌ Failed to create database backup');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            \Log::error('Database backup failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Clean up old backup files, keeping only the specified number
     */
    private function cleanupOldBackups($keep)
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/database_backup_*.sqlite');

        if (count($files) <= $keep) {
            return;
        }

        // Sort files by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Remove old files beyond the keep limit
        $filesToDelete = array_slice($files, $keep);

        foreach ($filesToDelete as $file) {
            if (unlink($file)) {
                $filename = basename($file);
                $this->info("🗑️  Removed old backup: {$filename}");
            }
        }

        if (count($filesToDelete) > 0) {
            $this->info("🧹 Cleaned up " . count($filesToDelete) . " old backup(s)");
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}