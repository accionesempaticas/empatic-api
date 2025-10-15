<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CloudBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:cloud-backup {--provider=local : Storage provider (local, s3, google)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cloud backup of the SQLite database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('☁️ Starting cloud backup...');

        $provider = $this->option('provider');

        try {
            // Get the database path
            $databasePath = database_path('database.sqlite');

            if (!file_exists($databasePath)) {
                $this->error('❌ Database file not found at: ' . $databasePath);
                return 1;
            }

            // Create backup filename with timestamp
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupFilename = "cloud_backup_{$timestamp}.sqlite";

            // Get file content
            $databaseContent = file_get_contents($databasePath);
            $fileSize = strlen($databaseContent);

            // Store in cloud based on provider
            switch ($provider) {
                case 's3':
                    if (!config('filesystems.disks.s3.key')) {
                        $this->error('❌ AWS S3 not configured. Set AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY');
                        return 1;
                    }
                    $stored = Storage::disk('s3')->put("backups/{$backupFilename}", $databaseContent);
                    $location = "AWS S3";
                    break;

                case 'google':
                    if (!config('filesystems.disks.gcs.key_file')) {
                        $this->error('❌ Google Cloud Storage not configured');
                        return 1;
                    }
                    $stored = Storage::disk('gcs')->put("backups/{$backupFilename}", $databaseContent);
                    $location = "Google Cloud Storage";
                    break;

                default:
                    // Fallback to local persistent volume
                    $backupDir = '/app/persistent-backups';
                    if (!is_dir($backupDir)) {
                        mkdir($backupDir, 0755, true);
                    }
                    $stored = file_put_contents("{$backupDir}/{$backupFilename}", $databaseContent);
                    $location = "Persistent Volume";
                    break;
            }

            if ($stored) {
                $this->info("✅ Cloud backup created successfully:");
                $this->info("   ☁️  Provider: {$location}");
                $this->info("   📁 File: {$backupFilename}");
                $this->info("   📊 Size: " . $this->formatBytes($fileSize));

                // Clean up old cloud backups
                $this->cleanupOldCloudBackups($provider);

                // Log backup creation
                \Log::info('Cloud backup created', [
                    'filename' => $backupFilename,
                    'provider' => $provider,
                    'location' => $location,
                    'size' => $fileSize,
                    'timestamp' => Carbon::now()
                ]);

                return 0;
            } else {
                $this->error('❌ Failed to create cloud backup');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Cloud backup failed: ' . $e->getMessage());
            \Log::error('Cloud backup failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Clean up old cloud backup files
     */
    private function cleanupOldCloudBackups($provider, $keep = 30)
    {
        try {
            switch ($provider) {
                case 's3':
                    $files = Storage::disk('s3')->files('backups');
                    $disk = Storage::disk('s3');
                    break;

                case 'google':
                    $files = Storage::disk('gcs')->files('backups');
                    $disk = Storage::disk('gcs');
                    break;

                default:
                    $backupDir = '/app/persistent-backups';
                    $files = glob($backupDir . '/cloud_backup_*.sqlite');
                    // For local files, we'll handle differently
                    if (count($files) > $keep) {
                        usort($files, function($a, $b) {
                            return filemtime($b) - filemtime($a);
                        });
                        $filesToDelete = array_slice($files, $keep);
                        foreach ($filesToDelete as $file) {
                            unlink($file);
                        }
                    }
                    return;
            }

            // For cloud providers
            $backupFiles = array_filter($files, function($file) {
                return strpos($file, 'cloud_backup_') !== false;
            });

            if (count($backupFiles) > $keep) {
                // Sort by last modified (newest first)
                usort($backupFiles, function($a, $b) use ($disk) {
                    return $disk->lastModified($b) - $disk->lastModified($a);
                });

                $filesToDelete = array_slice($backupFiles, $keep);
                foreach ($filesToDelete as $file) {
                    $disk->delete($file);
                    $this->info("🗑️  Removed old cloud backup: " . basename($file));
                }
            }

        } catch (\Exception $e) {
            $this->warn("⚠️  Could not clean up old cloud backups: " . $e->getMessage());
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