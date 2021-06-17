<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\V1\CronJobsController;
use App\User;

class ServicesCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to admins';

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
    public function handle(CronJobsController $push)
    {
        return $push->handleExecuteCron();
    }
}
