<?php

namespace App\Console\Commands;

use App\Group;
use App\Notifications\CustomNotification;
use App\User;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a notification for a user.';

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
        $username = $this->anticipate('Who do you want to send?', function ($input) {
            return User::all()->pluck('username')->toArray();
        });
        $user = User::firstWhere('username', $username);
        if ($user == null) {
            $this->error("User not found.");
            return;
        }
        $message = $this->ask('What message do you want to send?');
        if ($this->confirm("Do you want to set the title? (default: notifications.message_from_developers)"))
            $title = $this->ask("Enter the title");
        if ($this->confirm("Do you want to set the channel id? (default: other)"))
            $channel_id = $this->ask("Enter the channel id");
        if ($this->confirm("Do you want to set the payload?")) {
            if ($this->confirm("Do you want to set the screen in the payload?"))
                $screen = $this->ask("Enter screen");
            if ($this->confirm("Do you want to set the group in the payload?")) {
                $group_id = $this->ask("Enter group id (name and currency will be set accordingly)");
                $group = Group::find($group_id);
                if ($group == null) {
                    $this->error("Group not found.");
                    return;
                }
            }
            if ($this->confirm("Do you want to set the details in the payload?"))
                $details = $this->ask("Enter details");

            $payload = [
                'screen' => $screen ?? null,
                'group_id' => $group?->id ?? null,
                'group_name' => $group?->name ?? null,
                'currency' => $group?->currency ?? null,
                'details' => $details ?? null
            ];
        }
        $log = $this->confirm("Do you want to echo the generated message?");

        $user->notify((new CustomNotification($message, $title ?? null, $payload ?? null, $channel_id ?? null, $log))->locale($user->language));
        $this->info("Message sent to " . $user->username . ".");
    }
}
