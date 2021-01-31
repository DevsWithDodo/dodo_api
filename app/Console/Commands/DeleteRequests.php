<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Request;

class DeleteRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force deletes the trashed shopping requests.';

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
        Request::onlyTrashed()->forceDelete();
    }
}
