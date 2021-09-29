<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\UserFreeDays;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AddDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add_days {subscription} {days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command add days [Hourly,Real World,DELE,Medellin Real World,Medellin Real World 1199,Medellin Real World Lite,Medellin DELE]';

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
        $subscription_array=["Hourly","Real World","DELE","Medellin Real World","Medellin Real World 1199","Medellin Real World Lite","Medellin DELE"];
        $subscription=isset($subscription_array[$this->argument('subscription')])?$subscription_array[$this->argument('subscription')]:$this->argument('subscription');
        $days=$this->argument('days');
        $this->info("New Free Days from Admin - Subscription: ".$subscription." - Days: ".$days);
        Log::info("New Free Days from Admin - Subscription: ".$subscription." - Days: ".$days);
        $subscriptions=null;

        if($subscription=="Hourly") {
            $subscriptions=Subscription::where("plan_name","baselang_hourly")->get();
        }elseif($subscription=="Real World") {
            $subscriptions=Subscription::whereIn("plan_name",["baselang_99","baselang_99_trial","baselang_129","baselang_129_trial","baselang_149","baselang_149_trial"])->get();
        }elseif($subscription=="DELE") {
            $subscriptions=Subscription::where("plan_name","baselang_dele")->orwhere("plan","baselang_dele_trial")->get();
        }elseif($subscription=="Medellin Real World") {
            $subscriptions=Subscription::where("plan_name","medellin_rw")->orwhere("plan","medellin_rw_trial")->get();
        }elseif($subscription=="Medellin Real World 1199") {
            $subscriptions=Subscription::where("plan_name","medellin_rw_1199")->orwhere("plan","medellin_rw_1199_trial")->get();
        }elseif($subscription=="Medellin Real World Lite") {
            $subscriptions=Subscription::where("plan_name","medellin_rw_lite")->orwhere("plan","medellin_rw_lite_trial")->get();
        }elseif($subscription=="Medellin DELE") {
            $subscriptions=Subscription::where("plan_name","medellin_dele")->orwhere("plan","medellin_dele_trial")->get();
        }

        if($subscriptions && count($subscriptions)>0) {
            Log::info("Number of subscriptions ".$subscription." to verify: ".count($subscriptions));
            $this->info("Number of subscriptions ".$subscription." to verify: ".count($subscriptions));
            $count_subscriptions=0;
            foreach($subscriptions as $subs) {
                $user=$subs->user;
                if($user && ($subs->status=="active" || $subs->status=="future" || ($subs->status=="cancelled" && $subs->ends_at > gmdate("Y-m-d")))) {
                    $this->info("Adding days to ".$user->email);
                    $user->addFreeDays($days);
                    $user->updateSubscriptionInfo();
                    UserFreeDays::create(["user_id"=>$user->id, "referred_id"=>2100, "active"=>1, "claimed"=>1,"available"=>1, "free_days"=>$days, "admin"=>1]);
                    $count_subscriptions++;
                }
            }
            $this->info($days." free days have been added to ".$count_subscriptions." subscriptions ".$subscription."!");
        }else {
            $this->info("There are no ".$subscription." subscriptions currently!");
        }
    }
}
