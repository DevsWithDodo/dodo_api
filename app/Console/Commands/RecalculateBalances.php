<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RecalculateBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculate:balances {group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the member balances in a group.';

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
        $group = \App\Group::findOrFail($this->argument('group'));
        $group->recalculateBalances();
    }
}
