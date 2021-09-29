<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\UserFreeDays;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManualCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:manualcharge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warning on runing';

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
     * @return mixed
     */
    public function handle()
    {

        $result = \Chargebee_Subscription::create([
            'planId' => 'grammarless-online-1000paymentplan',
            'startDate' => '2019-08-04',
            'billingCycles'=>3
        ]);


        $this->info("DONE");
        return true;
    }
}
