<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resume;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class CleanupGuestResumes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumes:cleanup {--days=30 : Delete guest resumes older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete guest-created resumes older than N days and remove associated files';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Cleaning up guest resumes older than {$days} days (before {$cutoff})...");

        $query = Resume::whereNull('user_id')->where('created_at', '<', $cutoff);

        $count = $query->count();
        if ($count === 0) {
            $this->info('No guest resumes to delete.');
            return 0;
        }

        $this->info("Found {$count} guest resumes to delete. Processing...");

        $query->chunkById(100, function ($resumes) {
            foreach ($resumes as $resume) {
                // delete photo
                if ($resume->photo_path) {
                    try {
                        Storage::disk('public')->delete($resume->photo_path);
                    } catch (\Throwable $e) {
                        // continue
                    }
                }

                // delete relations
                try {
                    $resume->histories()->delete();
                    $resume->licenses()->delete();
                    $resume->profile()->delete();
                } catch (\Throwable $e) {
                }

                try {
                    $resume->delete();
                } catch (\Throwable $e) {
                }
            }
        });

        $this->info('Cleanup completed.');

        return 0;
    }
}
