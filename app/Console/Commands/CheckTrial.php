<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Log;

class CheckTrial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the trial version to false for non-elligible users.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::where('created_at', '<', now()->subWeeks(2))->where('trial', true)->get();
        // This is not the most efficient solution but it makes sure that the model updating event gets called which sets the user_state correctly.
        $users->each(fn (User $user) => $user->update(['trial' => false]));
        $this->info("Found " . $users->count() . " users.");
    }
}
