<?php
/**
 * Created by PhpStorm.
 * User: Personal
 * Date: 26/10/2017
 * Time: 2:48 PM
 */

namespace App\Http\Controllers;

use App\Models\ActiveDeleTrial;
use App\Models\ActiveLocation;
use App\Models\BlockDay;
use App\Models\BuyInmersion;
use App\Models\BuyPrebook;
use App\Models\Classes;
use App\Models\DeleTrialTest;
use App\Models\InmersionPayment;
use App\Models\PauseAccount;
use App\Models\Prebook;
use App\Models\Role;
use App\Models\ScheduledChanges;
use App\Models\Subscription;
use App\Models\UserCalendar;
use App\Models\UserCredits;
use App\Models\UserCreditsTransactions;
use App\Models\UserFreeDays;
use App\User;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{

    /*

    public function checkCredits(){
        $sales = \ChargeBee_Transaction::retrieve([
            \Chargebee_TransactionSearch::orderId()->startsWith('BC-'),
        ]);
        foreach($sales as $sale){
            $transaction=UserCreditsTransactions::where("chargebee_id",$sale->id)->first();
            if(!$transaction){
                $user = User::where("chargebee_id",$sale->customerDetails->id)->first();
                if(!$user){
                    \Log::error("User Does not exist".$sale->customerDetails->id);
                    continue;
                }

                \Log::error("Recovering credits for:".$sale->customerDetails->id." and ".$sale->orderId);
                UserCreditsTransactions::insert(["chargebee_id"=>$sale->id,"user_id"=>$sale->customerDetails->id]);
                $credits=explode("-",$sale->orderId)[1];
                User::where("chargebee_id",$sale->customerDetails->id)->update(["credits"=>$user->credits+$credits]);

            }
        };

        return "";
    }


    public function sendLinkEmail(){
        //10 minutes ago
        //Get next class

        $cron_minutes=gmdate('i');
        if(intval($cron_minutes)==50){
            $hour=intval(gmdate('H'))+1;
            if($hour<10){
                $hour="0".$hour;
            }
            $minutes="00";

            $nextClasses=Classes::where('class_time',gmdate("Y-m-d").' '.$hour.":".$minutes.":00")->get();
        } elseif(intval($cron_minutes)==20) {
            $minutes="30";

            $nextClasses=Classes::where('class_time',gmdate("Y-m-d H:").$minutes.":00")->get();

        } else {
            return response()->json(['success' => true]);
        }


        foreach($nextClasses as $nextClass){

            //if with same zoom id, less time, continue
            $firstBlockClass=Classes::where("zoom_id",$nextClass->zoom_id)->orderBy("class_time","asc")->first();

            if($firstBlockClass->id!=$nextClass->id){
                continue;
            }


            try {
                $user=$nextClass->student;
                $teacher=$nextClass->teacher;
                if(\App::environment('production'))
                {
                    \Mail::send('emails.student_class_remember', ["user"=>$user,"class"=>$nextClass], function($message) use($user,$teacher)
                    {
                        $message->subject("Class with ".$teacher->first_name." starting in 10 minutes");
                        $message->to($user->email,$user->first_name);
                    });
                }

            } catch(\Exception $e) {
                \Log::error("Error Sending Email:". $e->getMessage());
                \Log::info('Cron Job: Email Fail'. var_export($nextClass->user->email.' '.$nextClass->id,true));
                continue;
            }

        };

        return response()->json(['success' => true]);

    }

    public function changeScheduledPlans(){

        $scheduled_changes=ScheduledChanges::where("status",1)->where("change_date",gmdate("Y-m-d"))->get();
		foreach ($scheduled_changes as $scheduled_change)
		{
            try 
			{
				\Log::info('Checking Scheduled Change '. var_export($scheduled_change,true));
				User::where('id',$scheduled_change->user_id)->update(['last_unlimited_subscription'=>'baselang_149']);
				ScheduledChanges::where('id',$scheduled_change->id)->update(['status'=>0]);
			}
			catch (\Exception $e){
                \Log::error('Error Checking Scheduled Change'. var_export($e->getMessage(),true));
                continue;
            }
		}

        return response()->json(['success' => true]);

    }


    public function checkReferral()
    {
        $free_days=UserFreeDays::where("claimed",0)->where("available",1)->where("admin",0)->where("ref_activation_date",gmdate("Y-m-d"))->get();

        foreach ($free_days as $free_day)
        {

            try 
            {
                \Log::info('Checking free day '. var_export($free_day,true));
                $in_hourly=false;
                $ready=false;
                $refered = User::where("id",$free_day->referred_id)->first();
                $user = User::where("id",$free_day->user_id)->first();

                $all_subscription = \ChargeBee_Subscription::all(array(
                      "customerId[is]" => $refered->chargebee_id
                      ));

                if(!$all_subscription){
                    \Log::error("No found customer by ID ".$refered->chargebee_id);
                    continue;
                }

                foreach($all_subscription as $payment_method) 
                {
                    $subscription = $payment_method->subscription();
                    \Log::info('Subscription Info: '.var_export($subscription->status,true).' '.var_export($subscription->planId,true).' '.var_export($subscription->id,true));
                    
                    $ready=false;
                    if($subscription->status =='active' && $subscription->status !='in_trial'){
                        $ready=true;
                    }

                    $in_hourly=false;
                    if($subscription->planId=="baselang_hourly"){
                        $in_hourly=true;
                        $ready=false;
                    }
 
                    if($ready)
                    {
                        UserFreeDays::where("id",$free_day->id)->update(["claimed"=>1]);
                        $user->addFreeDays($free_day->free_days);
                        $localSubscription=$user->getCurrentSubscription();
                        \Log::info('Added Free days to: '.var_export($user->email,true).' '.var_export($subscription->planId,true).' '.var_export($subscription->id,true).' Subscription: '. $localSubscription);

                        try {
                            if(\App::environment('production')){
                                \Mail::send('emails.user_refered_free_mounth', ["user" => $user, "userRefered" => $refered], function ($message) use ($user) {
                                    $message->subject("You just got a free month of BaseLang!");
                                    $message->to($user->email, $user->first_name);
                                });
                            }
                        } catch (\Exception $e) {
                            Log::error('Cant send email: '.$e->getMessage());
                        }

                        break;
                    }

                    if($in_hourly || !$ready)
                    {

                        UserFreeDays::where("id",$free_day->id)->delete();

                        if(\App::environment('production')) 
                        {
                            \Mail::send('emails.user_refered_cancel', ["user"=>$user,"userRefered"=>$refered,"cancel"=>!$in_hourly], function($message) use($user,$in_hourly)
                            {
                                if($in_hourly){
                                    $message->subject("Your friend downgraded to BaseLang Hourly :(");
                                } else {
                                    $message->subject("Your friend didn't stay with BaseLang :(");
                                }
                                $message->to($user->email,$user->first_name);
                            });
                        }
                    }
                }

            } catch (\Exception $e){
                \Log::error('Error Checking Free Day'. var_export($e->getMessage(),true));
                continue;
            }
        }

        return response()->json(['success' => true]);
    }

    public function activeSubscriptions()
    {
        $paused_accounts=PauseAccount::all();

        foreach($paused_accounts as $paused_account) {
            $user = $paused_account->user;
            if($user && $paused_account->activation_day==gmdate("Y-m-d")){
                $user_subscription=$user->getCurrentSubscription();
                if(!$user_subscription || ($user_subscription && $user_subscription->status=="cancelled")){
                    try {
                        //if canceled with date => pending
                        if($user_subscription && $user_subscription->ends_at>gmdate("Y-m-d")){
                            $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->ends_at)->format("Y-m-d");
                            $result = \Chargebee_Subscription::create([
                                'planId' => $user->last_unlimited_subscription,
                                'startDate' => $start_date,
                            ]);
                        } else {

                            $result = \Chargebee_Subscription::create([
                                'planId' => $user->last_unlimited_subscription
                            ]);

                        }

                        if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                        Subscription::where("user_id",$user->id)->delete();
                        if($user->pause_account){
                            $user->pause_account->delete();
                        }
                        $user->updateSubscriptionInfo();
                    } catch (\Exception $e){
                        \Log::error("Error in catch activeSubscriptions - User: ".$user->email);
                    }
                }
            }
        }
    }

    public function automatedSubscriptionReminder()
    {
        $paused_accounts=PauseAccount::all();

        foreach($paused_accounts as $paused_account) {
            $user = $paused_account->user;
            Log::info('Pause Reminder sent: '.$user->email);
            if($user){
                $user_subscription=$user->getCurrentSubscription();
                $date=\DateTime::createFromFormat("Y-m-d",$paused_account->activation_day)->sub(new \DateInterval("P3D"))->format("Y-m-d");
                if((!$user_subscription || ($user_subscription && $user_subscription->status=="cancelled")) && $date==gmdate("Y-m-d")){
                    $token = str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789".uniqid());
                    $user->pause_account->update(["token"=>$token]);
                    try {
                        if(\App::environment('production')){
                            \Mail::send('emails.student_automated_subscription', ["user" => $user, "date" => $paused_account->activation_day, "token" => $token], function ($message) use ($user) {
                                $message->subject("Your BaseLang account is re-starting soon");
                                $message->to($user->email, $user->first_name);
                            });
                        }
                    } catch (\Exception $e) {
                        Log::error('Cant send email: '.$e->getMessage());
                    }
                }
            }
        }
    }

    public function activeDeleTrial()
    {
        $dele_trial_accounts=ActiveDeleTrial::all();

        foreach($dele_trial_accounts as $dele_trial) {
            $user = $dele_trial->user;
            if($user && $dele_trial->activation_day==gmdate("Y-m-d") && !$dele_trial->charge_dollar){
                Subscription::where("user_id",$user->id)->delete();

                Subscription::create(["status"=>"active","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan_name"=>"baselang_dele_test","starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")]);

                $dele_trial_test = new DeleTrialTest();
                $dele_trial_test->user_id = $user->id;
                $dele_trial_test->completed = 0;
                $dele_trial_test->ends_at_last_subscription = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d");
                $dele_trial_test->save();

                $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

                /*
                foreach($classes as $key => $class){
                    $class->removeZoom();
                    $class->delete();
                }
                */
            } else if ($user && $dele_trial->activation_day==gmdate("Y-m-d") && $dele_trial->charge_dollar==1){

                try {
                    $result = \ChargeBee_Transaction::createAuthorization([
                        'amount' => '1.00',
                        'options' => [
                            'submitForSettlement' => True,
                        ]
                    ]);

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    if($result->success){
                        Subscription::where("user_id",$user->id)->delete();

                        Subscription::create(["status"=>"active","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan_name"=>"baselang_dele_test","starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")]);

                        $dele_trial_test = new DeleTrialTest();
                        $dele_trial_test->user_id = $user->id;
                        $dele_trial_test->completed = 0;
                        $dele_trial_test->ends_at_last_subscription = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d");
                        $dele_trial_test->save();

                        $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                        $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

                        /*foreach($classes as $key => $class){
                            $class->removeZoom();
                            $class->delete();
                        }*/
                    }

                    \Log::info("Automatic Add DELE Test ".$user->email." ".$user->id);
                } catch(\Exception $e) {
                    if(isset($result)){
                        \Log::error('Error Payment Method: '.var_export($result,true));
                    } else {
                        \Log::error('Error Payment Method: '.var_export($e->getMessage(),true));
                    }
                }

            } else{
                dd("no llego");
            }
        }
    }

    public function activePrebook()
    {
        $buy_prebooks = BuyPrebook::where("status",1)->get();
        foreach($buy_prebooks as $buy_prebook) {
            $limit=\DateTime::createFromFormat("Y-m-d",$buy_prebook->activation_date)->add(new \DateInterval("P1Y"));

            if(!$limit){
                Log::info("Error loading limit for: ". $buy_prebook->id. " Activation Date: ". var_export($buy_prebook->activation_date,true). " type: " .var_export($buy_prebook->type,true)." limit: ".var_export($limit,true));
            }

            if($limit && gmdate("Y-m-d")>=$limit->format("Y-m-d")) {
                Log::info("Remove prebook for user: ". $buy_prebook->student->email . " Activation Date: ". var_export($buy_prebook->activation_date,true). " type: " .var_export($buy_prebook->type,true)." limit: ".var_export($limit,true));
                BuyPrebook::where("id",$buy_prebook->id)->update(["status"=>0]);
                Prebook::where("user_id",$buy_prebook->student->id)->delete();
            }
        }

        $current_day=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
        $day=$current_day->format("N");
        $prebooks = Prebook::where("day",$day)->get();
        Log::info("Getting Prebooks for today: ".gmdate("Y-m-d")." with N: ".$day." count: ".$prebooks->count());

        foreach($prebooks as $prebook) {
            try {
                if($prebook->student) {
                    Log::info("Do prebook for user: ". $prebook->student->email." with id: ".$prebook->id);
                    $first_date=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d ".$prebook->hour))->add(new \DateInterval("P7D"));
                    $second_date=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d ".$prebook->hour))->add(new \DateInterval("P14D"));

                    $user=$prebook->student;
                    $hourly=false;
                    if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly") {
                        $hourly = true;
                    }

                    if($user && $user->activated && $user->subscribed()) {
                    $classes=collect();
                    $first_class=Classes::where("teacher_id",$prebook->teacher->id)->where("class_time",$first_date->format("Y-m-d H:i:s"))->first();
                    $second_class=Classes::where("teacher_id",$prebook->teacher->id)->where("class_time",$second_date->format("Y-m-d H:i:s"))->first();

                    $studentTimezone = clone $first_date;
                    $studentTimezone->setTimezone(new \DateTimeZone($prebook->teacher->timezone));
                    $not_available=false;

                    $blocked_day = BlockDay::where("teacher_id",$prebook->teacher->id)->where("blocking_day",$studentTimezone->format("Y-m-d"))->first();

                    if($blocked_day && isset($blocked_day->from) && isset($blocked_day->till)) {

                        $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                        $time_from->setTimezone(new \DateTimeZone($prebook->teacher->timezone));
                        //Log::info($time_from->format("h:i:sa"));

                        $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                        $time_till->setTimezone(new \DateTimeZone($prebook->teacher->timezone));
                        //Log::info($time_till->format("h:i:sa"));

                        if($studentTimezone->format("H:i:s") >= $time_from->format("H:i:s") && $studentTimezone->format("H:i:s") <= $time_till->format("H:i:s")) {
                            //Log::info($time_from->format("H:i:sa")." ".$studentTimezone->format("H:i:sa")." ".$time_till->format("H:i:sa"));
                            $not_available = true;
                            Log::info("Class blocked - Teacher: ".$prebook->teacher->email." - Student: ".$prebook->student->email." - Class time: ".$first_date->format("Y-m-d H:i:s"));
                        }

                    }elseif($blocked_day && !isset($blocked_day->from) && !isset($blocked_day->till)) {
                        $not_available = true;
                    }

                    if(!$first_class && !$not_available){
                        if($hourly) {
                            if($user->credits==0) {
                                Log::info("No credits for Book prebook");
                                continue;
                            }

                                Log::info("User Credits for prebook: ".$user->credits);
                                $user->credits--;
                                Log::info("Credits -1 ".$user->credits);
                                User::where("id",$user->id)->update(["credits"=>$user->credits]);

                            }

                        $class = new Classes();
                        $class->user_id=$user->id;
                        $class->teacher_id=$prebook->teacher->id;
                        $class->class_time=$first_date->format("Y-m-d H:i:s");
                        $class->type=$prebook->type;
                        $class->save();
                        Log::info("Save class prebook #1: ".$class->id." - teacher_id: ".$class->teacher_id." - user_id: ".$class->user_id." ClassTime: ".$first_date->format("Y-m-d H:i:s"));
                        $class->createZoom($prebook->teacher);
                        $classes->push($class);
                    } else {
                        if($first_class) {
                            Log::info("Class booked for prebook #1: ".$first_class->id." - teacher_id: ".$first_class->teacher_id." - user_id: ".$first_class->user_id);
                        }
                        Log::info("Cannot Book prebook #1: ".$first_date->format("Y-m-d H:i:s")." - teacher_id: ".$prebook->teacher->id." - user_id: ".$user->email);
                    }

                    $studentTimezone = clone $second_date;
                    $studentTimezone->setTimezone(new \DateTimeZone($prebook->teacher->timezone));
                    $not_available=false;

                    $blocked_day = BlockDay::where("teacher_id",$prebook->teacher->id)->where("blocking_day",$studentTimezone->format("Y-m-d"))->first();

                    if($blocked_day && isset($blocked_day->from) && isset($blocked_day->till)) {

                        $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                        $time_from->setTimezone(new \DateTimeZone($prebook->teacher->timezone));
                        //Log::info($time_from->format("h:i:sa"));

                        $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                        $time_till->setTimezone(new \DateTimeZone($prebook->teacher->timezone));
                        //Log::info($time_till->format("h:i:sa"));

                        if($studentTimezone->format("H:i:s") >= $time_from->format("H:i:s") && $studentTimezone->format("H:i:s") <= $time_till->format("H:i:s")) {
                            //Log::info($time_from->format("H:i:sa")." ".$studentTimezone->format("H:i:sa")." ".$time_till->format("H:i:sa"));
                            $not_available = true;
                            Log::info("Class blocked - Teacher: ".$prebook->teacher->email." - Student: ".$prebook->student->email." - Class time: ".$second_date->format("Y-m-d H:i:s"));
                        }

                    }elseif($blocked_day && !isset($blocked_day->from) && !isset($blocked_day->till)) {
                        $not_available = true;
                    }

                    if(!$second_class && !$not_available){

                            if($hourly) {
                                if($user->credits==0) {
                                    Log::info("No credits for Book prebook");
                                    continue;
                                }

                                Log::info("User Credits for prebook: ".$user->credits);
                                $user->credits--;
                                Log::info("Credits -1 ".$user->credits);
                                User::where("id",$user->id)->update(["credits"=>$user->credits]);

                            }


                        $class = new Classes();
                        $class->user_id=$user->id;
                        $class->teacher_id=$prebook->teacher->id;
                        $class->class_time=$second_date->format("Y-m-d H:i:s");
                        $class->type=$prebook->type;
                        $class->save();
                        Log::info("Save class prebook #2: ".$class->id." - teacher_id: ".$class->teacher_id." - user_id: ".$class->user_id." ClassTime: ".$second_date->format("Y-m-d H:i:s"));
                        $class->createZoom($prebook->teacher);
                        $classes->push($class);
                    } else {
                        if($second_class) {
                            Log::info("Class booked for prebook #2: ".$second_class->id." - teacher_id: ".$second_class->teacher_id." - user_id: ".$second_class->user_id);
                        }
                        Log::info("Cannot Book prebook #2: ".$second_date->format("Y-m-d H:i:s")." - teacher_id: ".$prebook->teacher->id." - user_id: ".$user->email);
                    }

                        if(count($classes)>0) {
                            try {
                                if(\App::environment('production')){
                                    \Mail::send('emails.student_class_confirmed_prebook', ["user"=>$user,"classes"=>$classes], function($message) use($user)
                                    {
                                        $message->subject(__('Class Confirmed'));
                                        $message->to($user->email,$user->first_name);
                                    });
                                }

                                if(\App::environment('production')){
                                    $teacher=$prebook->teacher;
                                    \Mail::send('emails.teacher_class_confirmed_prebook', ["user"=>$user,"teacher"=>$teacher,"classes"=>$classes], function($message) use($teacher)
                                    {
                                        $message->subject(__('Class Confirmed'));
                                        $message->to($teacher->email,$teacher->first_name);
                                    });
                                }
                            } catch (\Exception $e) {
                                Log::error('Cant send email: '.$e->getMessage());
                            }
                        } else {
                            Log::info("No prebook classes for user: ".var_export($prebook->user_id,true)." with prebook: ".$prebook->id." type: ".$prebook->type);
                        }
                    } else {
                        Log::info("No prebook saved for user: ".var_export($prebook->student->email,true).", user without subscription, prebook: ".$prebook->id." type: ".$prebook->type);
                    }
                }else {
                    Log::info("User not exist. Prebook ID: ".$prebook->id);
                }
            } catch (\Exception $e) {
                Log::error('Error in Prebook - Cron. '. var_export($prebook,true));
            }
        }
    }

    public function secondPaymentImmersion($date=false)
    {
        if($date){
            $inmersions = BuyInmersion::where("second_payment_date", $date)->where("status", 0)->get();
        } else {
            $inmersions = BuyInmersion::where("second_payment_date", gmdate("Y-m-d"))->where("status", 0)->get();
        }

        Log::info("Inmersions to pay today ".gmdate("Y-m-d").": ".count($inmersions)."\n");
        foreach($inmersions as $inmersion) {
            Log::info("Inmersion ID: ".$inmersion->id);

            $user = $inmersion->student;
            if($user) {
                try {
                    \Log::info("Second payment of Immersion for: ".$user->email." - Weeks: ".$inmersion->weeks." - Amount: $".($inmersion->total_price/2)." - PMT: ".$user->payment_method_token);

                    $result = \ChargeBee_Invoice::chargeAddon(array(
						'subscriptionId' => $user->subscription->subscription_id,
                        'addonId' => 'inmersion_second_payment_600_addon',
						'addonQuantity' => 1
					));
					
					if(!$result->invoice()){ 
						throw new \Exception($result);
					}
					else {
						BuyInmersion::where("id", $inmersion->id)->update(["status"=>1]);
					}
				}
				catch (\Exception $e){
                    if(isset($result)){
                        \Log::error('Error of second payment of Inmersion: '.var_export($result,true));
                    } else {
                        \Log::error('Error of second payment of Inmersion: '.var_export($e->getMessage(),true));
                    }
                    if(\App::environment('production')) {
                        \Mail::send('emails.student_error_payment', ["inmersion"=>$inmersion], function($message) use($user)
                        {
                            $message->subject("Error: Second payment of Immersion");
                            $message->to("niall@baselang.com", "Niall");
                        });
                    }
                }
            }
        }
    }

    public function removeUsers()
    {
        /*
        $inmersion_payments = InmersionPayment::all();

        foreach($inmersion_payments as $inmersion_payment) {
            $user = $inmersion_payment->student;
            if($user) {
                if($user->chargebee_id) {
                    $result = \ChargeBee_Customer::delete($user->chargebee_id);
                    Log::info("User to delete: ".$user->email." - Removing Chargebee ID: ".$user->chargebee_id." - Status:".var_export($result->success,true));
                }else {
                    Log::info("User to delete: ".$user->email);
                }
                User::where("id",$user->id)->delete();
            }
        }
        */
    }

    public function activeLocation()
    {
        $email_reminders = ActiveLocation::where("date_to_schedule",gmdate("Y-m-d"))->get();
        Log::info("Email reminders to send today ".gmdate("Y-m-d").": ".count($email_reminders));
        foreach($email_reminders as $email_reminder) {
            $user = $email_reminder->user;
            if($user) {
                try {
                    if(\App::environment('production')) {
                        \Mail::send('emails.student_online_to_location', ["user" => $user, "email_reminder" => $email_reminder], function ($message) use ($user, $email_reminder) {
                            $message->subject("Schedule your first Medellin Spanish class");
                            $message->to($user->email, $user->first_name);
                        });
                    }
                } catch (\Exception $e) {
                    Log::error('Cant send email: '.$e->getMessage());
                }
            }
        }

        $trial_paydays = ActiveLocation::where("trial_payday",gmdate("Y-m-d"))->get();
        Log::info("Trial payday today ".gmdate("Y-m-d").": ".count($trial_paydays));
        foreach($trial_paydays as $trial_payday){
            $user = $trial_payday->user;
            if($user) {

                try {
                    $result = \ChargeBee_Transaction::createAuthorization([
                        'amount' => '1.00',
                        'options' => [
                            'submitForSettlement' => True,
                        ]
                    ]);

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    if($result->success){
                        Subscription::where("user_id",$user->id)->delete();

                        Subscription::create(["status"=>"active", "user_id"=>$user->id, "subscription_id"=>"BaseLang", "plan_name"=>$trial_payday->plan, "starts_at"=>gmdate("Y-m-d"), "ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P1D"))->format("Y-m-d")]);
                    }

                    \Log::info("Automatic Add Location Test ".$user->email." ".$user->id);
                } catch(\Exception $e) {
                    if(isset($result)){
                        \Log::error('Error Payment Method: '.var_export($result,true));
                    } else {
                        \Log::error('Error Payment Method: '.var_export($e->getMessage(),true));
                    }
                }

            }
        }


        $active_location = ActiveLocation::where("activation_day",gmdate("Y-m-d"))->get();
        Log::info("Subscriptions to activate today ".gmdate("Y-m-d").": ".count($active_location));
        foreach($active_location as $activate_now) {
            $user = $activate_now->user;
            if($user) {

                $user_subscription=$user->getCurrentSubscription();

                if($user_subscription && in_array($user_subscription->plan,["medellin_RW","medellin_DELE"])){
                    Log::info("Past plan: ".$user_subscription->plan);
                    Subscription::where("user_id",$user->id)->delete();
                    $user->updateSubscriptionInfo();
                    $user_subscription=$user->getCurrentSubscription();
                    if($user_subscription){
                        Log::info("Present plan: ".$user_subscription->plan);
                    }
                }

                if($user_subscription){
                    
                    try {

                        $changes=['price'=>$activate_now->price, 'planId'=>$activate_now->plan, 'options'=>['prorateCharges'=>true]];
                        $result=\ChargeBee_Subscription::update($user_subscription->subscription_id, $changes);
                        $user->last_unlimited_subscription=$activate_now->plan;
                        User::where("id",$user->id)->update(["last_unlimited_subscription"=>$activate_now->plan]);

                        if($result->success) {

                            Log::info("User: ".$user->email." - Change of plan online (".$user_subscription->plan.") to plan in-person (".$activate_now->plan.")");

                            try {
                                if(\App::environment('production')) {
                                    //Email to Niall and Thomas
                                    $subscription=$activate_now->plan;
                                    \Mail::send('emails.new_user_location', ["user" => $user, "subscription" => $subscription], function ($message) use ($user, $subscription) {
                                        $message->subject("New student - ".$subscription);
                                        $message->bcc(['niall@baselang.com' => 'Niall', 'thomas.codetosuccess@gmail.com' => 'Thomas']);
                                    });
                                }
                            } catch (\Exception $e) {
                                Log::error('Cant send email: '.$e->getMessage());
                            }

                            $activate_now->delete();
                            $user->updateSubscriptionInfo();

                        } 

                    } catch (\Exception $e) {
                        Log::error('Error Updating Subscription ID: '.var_export($e->getMessage(),true));
                    }

                }elseif(!$user->subscribed()){

                    try {
                        $result = \Chargebee_Subscription::create([
                            'planId' => $activate_now->plan
                        ]);

                        $subscription = $activate_now->plan;

                        if($result->success) {
                            
                            Log::info("New plan in-person (".$subscription.")");
                            
                            try {
                                if(\App::environment('production')) {
                                    //Email to Niall and Thomas
                                    \Mail::send('emails.new_user_location', ["user" => $user, "subscription" => $subscription], function ($message) use ($user, $subscription) {
                                        $message->subject("New student - ".$subscription);
                                        $message->bcc(['niall@baselang.com' => 'Niall', 'thomas.codetosuccess@gmail.com' => 'Thomas']);
                                    });
                                }
                            } catch (\Exception $e) {
                                Log::error('Cant send email: '.$e->getMessage());
                            }

                            $activate_now->delete();
                            Subscription::where("user_id",$user->id)->delete();
                            $user->updateSubscriptionInfo();

                        }

                        if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                        Log::info("Subscription Done: ".$user->email." Subscription: ".$subscription." Result: ".var_export($result->subscription->id,true));

                        Subscription::where("user_id",$user->id)->delete();
                    } catch (\Exception $e){
                        Log::error("Subscription Error: ".$user->email." Subscription: ".$activate_now->plan." Result: ".var_export($e->getMessage(),true));
                    }

                }

            }
        }
    }*/
}
