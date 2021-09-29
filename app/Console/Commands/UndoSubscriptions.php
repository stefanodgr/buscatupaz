<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UndoSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:undo_subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-subscribe RW canceled';

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
        $subscriptions=Subscription::where('ends_at','>=',gmdate('Y-m-d'))->whereIn('status',['Active','Pending'])->whereIn('plan',$consult_subscriptions)->get();
        $this->info('Subscriptions to verify: '.count($subscriptions).' in '.gmdate('Y-m-d'));
        foreach($subscriptions as $subscription) {

            $user = $subscription->user;
            if (!$user) {
                continue;
            }
            $this->info('Checking User: ' . $user->email . ' Plan: ' . $subscription->plan->name . ' - ID: ' . $subscription->subscription_id . ' LUS: ' . $user->last_unlimited_subscription);

            if (($user->last_unlimited_subscription == "baselang_dele" || $user->last_unlimited_subscription == "baselang_dele_trial") || $subscription->plan->name == "baselang_dele" || $subscription->plan->name == "baselang_dele_trial") {
                continue;
            }
            $check_subscription = false;
            if ($user && $user->chargebee_id) {
                try {
                    $customer = \ChargeBee_Customer::retrieve($user->chargebee_id);
                    if ($customer) {
                        foreach ($customer->paymentMethods as $payment_method) {
                            foreach ($payment_method->subscriptions as $subs) {
                                if ($subs->status == \Chargebee_Subscription::ACTIVE || $subs->status == \Chargebee_Subscription::PENDING) {
                                    $check_subscription = true;
                                    $this->error('Manual check: '.$subs->id);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::info('Error: ' . $e->getMessage() . ' - User: ' . $user->email);
                }

                if ($check_subscription) {
                    //TIENE ACTIVA O PENDING; ESTA OK
                    continue;
                }

                if ($user->payment_method_token) {
                    if ($subscription->status == 'Active') {
                        $first_billing_date = $subscription->ends_at;
                    } else {
                        //Pending
                        $first_billing_date = $subscription->starts_at;
                    }

                    if ($first_billing_date < gmdate('Y-m-d')) {
                        $this->error('First Billing Lower Than Today: ' . $user->email);
                        continue;
                    }

                    if ($subscription->plan->name == 'baselang_99_trial') {
                        $subscription->plan->name = 'baselang_99';
                        Subscription::where('id', $subscription->id)->update(['plan' => 'baselang_99']);
                    } elseif ($subscription->plan->name == 'baselang_129_trial') {
                        $subscription->plan->name = 'baselang_129';
                        Subscription::where('id', $subscription->id)->update(['plan' => 'baselang_129']);
                    } elseif ($subscription->plan->name == 'baselang_149_trial') {
                        $subscription->plan->name = 'baselang_149';
                        Subscription::where('id', $subscription->id)->update(['plan' => 'baselang_149']);
                    }

                    $result = \Chargebee_Subscription::create([
                        'planId' => $subscription->plan->name,
                        'firstBillingDate' => $first_billing_date,
                    ]);
                } else {
                    $this->error('Usuario sin pago: ' . $user->email);
                }
            }
        }

        $this->info('End of the verification!');
        return true;

        }
}
