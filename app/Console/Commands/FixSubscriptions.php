<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fix_subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix subscriptions 99-129';

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
        $consult_subscriptions=['baselang_129_trial','baselang_129','baselang_149_trial','baselang_149'];
        $subscriptions=Subscription::where('ends_at','>=',gmdate('Y-m-d'))->get();
        $this->info('Subscriptions to verify: '.count($subscriptions));
        foreach($subscriptions as $subscription){

            $user=$subscription->user;
            if(!$user){
                continue;
            }
            $this->info('Checking User: '.$user->email.' Plan: '.$subscription->plan->name.' - ID: '.$subscription->subscription_id. ' LUS: '.$user->last_unlimited_subscription);

            if(($user->last_unlimited_subscription=="baselang_dele" || $user->last_unlimited_subscription=="baselang_dele_trial") || $subscription->plan->name=="baselang_dele" || $subscription->plan->name=="baselang_dele_trial"){
                continue;
            }
            $check_subscription=null;
            $is_baselang_99=false;
            $is_baselang_129=false;
            $is_baselang_149=false;
            if($user && $user->chargebee_id){
                try{
                    $customer=\ChargeBee_Customer::retrieve($user->chargebee_id);
                    if($customer){
                        foreach($customer->paymentMethods as $payment_method){
                            foreach($payment_method->subscriptions as $subs){
                                if($subs->status==\Chargebee_Subscription::ACTIVE || $subs->status==\Chargebee_Subscription::PENDING){
                                    try{
                                        if(!in_array($subs->planId,$consult_subscriptions)){
                                            continue;
                                        }
                                        $check_subscription = $subs;
                                        $result=\Chargebee_Subscription::cancel($subs->id);
                                        $this->info('Subscripción cancelada: '.$subs->id);
                                        if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                                    }catch(\Exception $e){
                                        Log::error('Error Canceling Subscription: '.var_export($subs->id,true));
                                        Log::error(var_export($e->getLine(),true));
                                        Log::error(var_export($e->getMessage(),true));
                                    }
                                }

                                if($subs->planId=='baselang_99' || $subs->planId=='baselang_99_trial'){
                                    $is_baselang_99=true;
                                }

                                if($subs->planId=='baselang_129' || $subs->planId=='baselang_129_trial'){
                                    $is_baselang_129=true;
                                }

                                if($subs->planId=='baselang_149' || $subs->planId=='baselang_149_trial'){
                                    $is_baselang_149=true;
                                }
                            }
                        }
                    }
                } catch(\Exception $e) {
                    Log::info('Error: '.$e->getMessage().' - User: '.$user->email);
                }

                if(!$check_subscription){
                    continue;
                }

                if($is_baselang_99){
                    if($check_subscription->status!=\Chargebee_Subscription::ACTIVE && $check_subscription->status!=\Chargebee_Subscription::PENDING) {
                        continue;
                    }

                    try{
                        $result = \Chargebee_Subscription::create([
                            'planId'=>'baselang_99',
                            'firstBillingDate'=>$check_subscription->nextBillingDate->format('Y-m-d'),
                        ]);
                    }catch(\Exception $e){
                        Log::error('Error on created subscription'.var_export($subscription,true));
                        Log::error(var_export($e->getMessage(),true));
                        Log::error(var_export($e->getLine(),true));
                    }
                    Subscription::where('user_id',$user->id)->delete();
                    User::where('id',$user->id)->update(['last_unlimited_subscription'=>'baselang_99']);
                    $this->info('User: '.$user->email.' - From : '.$check_subscription->planId.' - To: baselang_99');
                } elseif ($is_baselang_129) {

                    if($check_subscription->status!=\Chargebee_Subscription::ACTIVE && $check_subscription->status!=\Chargebee_Subscription::PENDING){
                        continue;
                    }

                    try{
                        $result = \Chargebee_Subscription::create([
                            'planId'=>'baselang_129',
                            'firstBillingDate'=>$check_subscription->nextBillingDate->format('Y-m-d'),
                        ]);
                    }catch(\Exception $e){
                        Log::error(var_export($e->getMessage(),true));
                    }

                    Subscription::where('user_id',$user->id)->delete();
                    User::where('id',$user->id)->update(['last_unlimited_subscription'=>'baselang_129']);
                    $this->info('User: '.$user->email.' - From : '.$check_subscription->planId.' - To: baselang_129');
                } elseif($is_baselang_149) {
                    if($check_subscription->status!=\Chargebee_Subscription::ACTIVE && $check_subscription->status!=\Chargebee_Subscription::PENDING){
                        continue;
                    }

                    try{
                        $result = \Chargebee_Subscription::create([
                            'planId'=>'baselang_149',
                            'firstBillingDate'=>$check_subscription->nextBillingDate->format('Y-m-d'),
                        ]);
                    }catch(\Exception $e){
                        Log::error(var_export($e->getMessage(),true));
                    }

                }
            }
        }
        $this->info('End of the verification!');
        return true;
    }

}
