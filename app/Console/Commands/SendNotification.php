<?php

namespace App\Console\Commands;

use App\Notifications\CustomNotification;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification {user_id} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a notification for the given user.';

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
        $user = \App\User::findOrFail($this->argument('user_id'));
        $user->notify(new CustomNotification($this->argument('message')));
        $this->info("Message sent to " . $user->username);
    }
}
