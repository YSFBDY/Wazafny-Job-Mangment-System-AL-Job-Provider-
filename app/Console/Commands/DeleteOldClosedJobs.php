<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Jobpost; 
use Carbon\Carbon;

class DeleteOldClosedJobs extends Command
{

    protected $signature = 'jobs:delete-old-closed-jobs';

    protected $description = 'Delete jobs that have been closed for more than a year';

    public function handle()
    {
        $cutoff = Carbon::now()->subYear();

        $jobs = Jobpost::where('job_status', 'closed')
            ->where('updated_at', '<', $cutoff)
            ->get();

        $count = $jobs->count();

        foreach ($jobs as $job) {
            $job->delete();
        }

        $this->info("Deleted $count closed jobs older than one year.");
        return 0;
    
    }
}
