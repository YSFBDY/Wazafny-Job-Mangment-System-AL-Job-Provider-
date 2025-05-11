<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;


class DeleteUnverifiedUsers extends Command
{
    protected $signature = 'users:cleanup-unverified';
    protected $description = 'Delete users who did not verify within 4 minutes';

    public function handle()
    {
        $cutoff = Carbon::now()->subMinutes(4);

        $deleted = User::where('verified', false)
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted $deleted unverified users.");
        Log::info("Deleted $deleted unverified users.");
    }
}
