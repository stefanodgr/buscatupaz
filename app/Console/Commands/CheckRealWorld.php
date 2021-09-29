<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;

class CheckRealWorld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check_rw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Real World';

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
        $users = Role::where('name','student')->first()->users()->where("activated",1)->orderBy("id","ASC")->get();
        $this->info("Users to verify: ".count($users)."\n");

        foreach($users as $user) {
            $last_unlimited_subscription = $user->last_unlimited_subscription;
            $subscriptions = $user->subscriptions->sortByDesc("created_at");

            if(count($subscriptions)>0) {

                $collect_subscriptions=collect();
                $verify=[];

                foreach($subscriptions as $subscription) {
                    $collect_subscriptions->push($subscription);

                    foreach($collect_subscriptions as $subs) {

                        if($subs->plan=="baselang_99" && ($subscription->plan->name=="baselang_129" || $subscription->plan->name=="baselang_129_trial" || $subscription->plan->name=="baselang_149" || $subscription->plan->name=="baselang_149_trial")) {

                            if(!isset($verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan])){
                                $verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan]=1;
                                $this->info("User: ".$user->email." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan);
                            }
                        }

                        if($subs->plan=="baselang_99" && ($last_unlimited_subscription=="baselang_129" || $last_unlimited_subscription=="baselang_129_trial" || $last_unlimited_subscription=="baselang_149" || $last_unlimited_subscription=="baselang_149_trial")) {
                            
                            if(!isset($verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription])){
                                $verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription]=1;
                                $this->info("User: ".$user->email." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription);
                            }
                        }

                        if($subs->plan=="baselang_99_trial" && ($subscription->plan->name=="baselang_129" || $subscription->plan->name=="baselang_129_trial" || $subscription->plan->name=="baselang_149" || $subscription->plan->name=="baselang_149_trial")) {

                            if(!isset($verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan])){
                                $verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan]=1;
                                $this->info("User: ".$user->email." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan);
                            }
                        }

                        if($subs->plan=="baselang_99_trial" && ($last_unlimited_subscription=="baselang_129" || $last_unlimited_subscription=="baselang_129_trial" || $last_unlimited_subscription=="baselang_149" || $last_unlimited_subscription=="baselang_149_trial")) {
                            
                            if(!isset($verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription])){
                                $verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription]=1;
                                $this->info("User: ".$user->email." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription);
                            }
                        }

                        if($subs->plan=="baselang_129" && ($subscription->plan->name=="baselang_99" || $subscription->plan->name=="baselang_99_trial" || $subscription->plan->name=="baselang_149" || $subscription->plan=="baselang_149_trial")) {

                            if(!isset($verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan])){
                                $verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan]=1;
                                $this->info("User: ".$user->email." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan);
                            }
                        }

                        if($subs->plan=="baselang_129" && ($last_unlimited_subscription=="baselang_99" || $last_unlimited_subscription=="baselang_99_trial" || $last_unlimited_subscription=="baselang_149" || $last_unlimited_subscription=="baselang_149_trial")) {
                            
                            if(!isset($verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription])){
                                $verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription]=1;
                                $this->info("User: ".$user->email." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription);
                            }
                        }

                        if($subs->plan=="baselang_129_trial" && ($subscription->plan->name=="baselang_99" || $subscription->plan->name=="baselang_99_trial" || $subscription->plan->name=="baselang_149" || $subscription->plan->name=="baselang_149_trial")) {

                            if(!isset($verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan])){
                                $verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan]=1;
                                $this->info("User: ".$user->email." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan);
                            }
                        }

                        if($subs->plan=="baselang_129_trial" && ($last_unlimited_subscription=="baselang_99" || $last_unlimited_subscription=="baselang_99_trial" || $last_unlimited_subscription=="baselang_149" || $last_unlimited_subscription=="baselang_149_trial")) {
                            
                            if(!isset($verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription])){
                                $verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription]=1;
                                $this->info("User: ".$user->email." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription);
                            }
                        }

                        if($subs->plan=="baselang_149" && ($subscription->plan->name=="baselang_99" || $subscription->plan->name=="baselang_99_trial" || $subscription->plan->name=="baselang_129" || $subscription->plan->name=="baselang_129_trial")) {

                            if(!isset($verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan])){
                                $verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan]=1;
                                $this->info("User: ".$user->email." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan);
                            }
                        }

                        if($subs->plan=="baselang_149" && ($last_unlimited_subscription=="baselang_99" || $last_unlimited_subscription=="baselang_99_trial" || $last_unlimited_subscription=="baselang_129" || $last_unlimited_subscription=="baselang_129_trial")) {
                            
                            if(!isset($verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription])){
                                $verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription]=1;
                                $this->info("User: ".$user->email." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription);
                            }
                        }

                        if($subs->plan=="baselang_149_trial" && ($subscription->plan->name=="baselang_99" || $subscription->plan->name=="baselang_99_trial" || $subscription->plan->name=="baselang_129" || $subscription->plan->name=="baselang_129_trial")) {

                            if(!isset($verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan])){
                                $verify["User: ".$user->id." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan]=1;
                                $this->info("User: ".$user->email." - Subs #1: ".$subs->plan." - Subs #2: ".$subscription->plan);
                            }
                        }

                        if($subs->plan=="baselang_149_trial" && ($last_unlimited_subscription=="baselang_99" || $last_unlimited_subscription=="baselang_99_trial" || $last_unlimited_subscription=="baselang_129" || $last_unlimited_subscription=="baselang_129_trial")) {
                            
                            if(!isset($verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription])){
                                $verify["User: ".$user->id." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription]=1;
                                $this->info("User: ".$user->email." - Subs: ".$subs->plan." - Last Unl Subs: ".$last_unlimited_subscription);
                            }
                        }
                    }
                }
            }
        }

        $this->info("\nEnd of the verification!");
    }
}
