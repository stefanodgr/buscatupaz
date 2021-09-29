<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\User;
use Illuminate\Console\Command;

class CreateSubscriptionTrial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:create_subscription_trial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Subscription Trial';

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
        $user_id = $this->ask('Enter the user_id to create the baselang_129_trial subscription');
        $user = User::where("id",$user_id)->first();

        if($user) {
            $chargebee_env = env('CHARGEBEE_ENV');
            $this->info("User: ".$user->email);

            if($chargebee_env=="sandbox") {
                try {
                    $result = \Chargebee_Subscription::create([
                        'planId' => "baselang_129_trial",
                        'trialPeriod' => true
                    ]);

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    if($result->success) {
                        Subscription::where("user_id",$user->id)->delete();
                        $this->info("Subscription created successfully!");
                    }
                }catch (\Exception $e) {
                    $this->info("Payment method not validated");
                }
            }else {
                $this->info("The CHARGEBEE_ENV is in production, not in sandbox!");
            }
        }else {
            $this->info("User ".$user_id." does not exist");
        }
    }
}
