<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class LogoutInactiveUsers extends Command
{

    protected $signature = 'users:logout-inactive-users';

    protected $description = 'Logout users whose latest token is older than one month';

    public function handle()
    {
        $cutoff = Carbon::now()->subMonth();

        // Group tokens by user_id and get their latest token
        $usersWithOldTokens = PersonalAccessToken::select('tokenable_id',)
            ->where('created_at', '<', $cutoff)
            ->groupBy('tokenable_id')
            ->get();

        $count = 0;

        foreach ($usersWithOldTokens as $user) {
            // Get the most recent token for this user
            $latestToken = PersonalAccessToken::where('tokenable_id', $user->tokenable_id)
                ->latest('created_at')
                ->first();

            if ($latestToken && $latestToken->created_at < $cutoff) {
                // Delete all tokens for this user
                PersonalAccessToken::where('tokenable_id', $user->tokenable_id)->delete();
                $count++;
            }
        }

        $this->info("Logged out $count users.");
        return 0;
    
    }
}
