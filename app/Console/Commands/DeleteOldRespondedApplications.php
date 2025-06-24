<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Application; 
use Carbon\Carbon;

class DeleteOldRespondedApplications extends Command
{

    protected $signature = 'applications:delete-old-responded-applications';

    protected $description = 'Delete job applications that are not pending and older than 2 weeks';

    public function handle()
    {
        $cutoff = Carbon::now()->subWeeks(2);

        $applications = Application::where('status', '!=', 'pending')
            ->where('updated_at', '<', $cutoff)
            ->get();

        $count = $applications->count();

        foreach ($applications as $application) {
            $application->delete();
        }

        $this->info("Deleted $count responded applications older than 2 weeks.");
        return 0;
    }
}
