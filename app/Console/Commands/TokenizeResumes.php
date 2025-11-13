<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resume;

class TokenizeResumes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumes:tokenize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate public_token for anonymous resumes (user_id NULL) that lack a token.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning resumes without public_token...');
        $query = Resume::whereNull('user_id')->whereNull('public_token');
        $count = $query->count();
        if ($count === 0) {
            $this->info('No anonymous resumes without tokens found.');
            return 0;
        }

        $this->info("Found {$count} resumes. Generating tokens...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunkById(100, function($resumes) use ($bar) {
            foreach ($resumes as $r) {
                $r->public_token = bin2hex(\random_bytes(16));
                $r->save();
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Token generation completed.');
        return 0;
    }
}
