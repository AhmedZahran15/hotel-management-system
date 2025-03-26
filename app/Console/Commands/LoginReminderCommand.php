<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MissYouNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LoginReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:login-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to users who have not logged in for a month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        // Find users who haven't logged in for a month
        $users = User::where('last_login_at', '<', $oneMonthAgo)
            ->orWhereNull('last_login_at')
            ->get();

        $count = 0;

        foreach ($users as $user) {
            $user->notify(new MissYouNotification());
            $count++;
        }

        $this->info("Reminder emails sent to {$count} users.");

        return Command::SUCCESS;
    }
}
