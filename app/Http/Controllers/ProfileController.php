<?php

namespace App\Http\Controllers;

use App\Http\Helper\CalendarEvent;
use App\Http\Helper\CalendarHelper;
use App\Models\ActiveDeleTrial;
use App\Models\ActiveLocation;
use App\Models\BuyPrebook;
use App\Models\Classes;
use App\Models\DeleTrialTest;
use App\Models\Error;
use App\Models\Feedback;
use App\Models\GoogleClient;
use App\Models\Level;
use App\Models\Location;
use App\Models\PauseAccount;
use App\Models\Prebook;
use App\Models\Role;
use App\Models\Statistics;
use App\Models\Subscription;
use App\Models\TokenReset;
use App\Models\UserCalendar;
use App\Models\UserCancellation;
use App\Models\UserCredits;
use App\Models\Plan;
use App\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "basic_info");
    }

    public function buyCredits(Request $request){
        $user = User::getCurrent();

        $credit_price=UserCredits::getCreditsPrice($request->get("valuetobuy"));

        $total=($credit_price*$request->get("valuetobuy")/2);
        $credits=$request->get("valuetobuy");

        try {
            Log::info("Credits to buy by: ".$user->email." credits:".$credits." last credits:".$user->credits);
            $result = \ChargeBee_Transaction::createAuthorization([
                'amount' => $total.'',
                'paymentMethodToken' => $user->payment_method_token,
                'descriptor' => [
                    'name' => 'BASELANG    *Credits'
                ],
                'options' => [
                    'submitForSettlement' => True,
                    'paypal' => [
                        'description'=> 'BaseLang Class Credits'
                    ]
                ]
            ]);

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

            $user->credits+=$credits;
            //$user->save();
            Log::info("Saving credits for ".$user->email." ".$user->id." : ".var_export($user->credits,true));
            User::where("id",$user->id)->update(["credits"=>$user->credits]);
            UserCredits::create(["credits"=>$credits,"user_id"=>$user->id,"billing_cycle"=>0,"subscription_id"=>$result->transaction->id]);
        } catch(\Exception $e) {
            if(isset($result)){
                Log::error('Error Payment Method: '.var_export($result,true));
            } else {
                Log::error('Error Payment Method: '.var_export($e->getMessage(),true));
            }

            return redirect()->route("credits")->withErrors(['Your payment method rejected the charge. Please try again, contact your bank or <a href="'.route("change_card").'">Click here to change your payment method</a>']);

        }
        
        return redirect()->route("credits")->with(["message_info"=>'Thanks for your purchase! We\'ve added '.$credits.' credit'.($credits==1?"":"s").' to your account.']);

    }

    public function cantAfford(){
        $user = User::getCurrent();
        Statistics::create(["user_id"=>$user->id,"type"=>"Cancelation","data_x"=>"cancel_free_time"]);
        return redirect()->route("referral_page");
    }

    public function cancelToHourlySubscription(){
        $user = User::getCurrent();

        if(!$user->subscribed()){
            try {
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => "baselang_hourly"
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            } catch (\Exception $e){
                return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
            }
        } else {
            $user_subscription=$user->getCurrentSubscription();

            User::where("id",$user->id)->update(["last_unlimited_subscription"=>$user_subscription->plan]);

            try {
                Subscription::where("id",$user_subscription->id)->delete();
                $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            } catch (\Exception $e){
                Subscription::create($user_subscription->toArray());
                return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
            }

            if($user_subscription->status=="future"){
                $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->starts_at);
            } else {
                $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->ends_at);
            }
            try {
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => "baselang_hourly",
                    'firstBillingDate' => $start_date,
                ]);
                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            } catch (\Exception $e){
                return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
            }

        }

        Statistics::create(["user_id"=>$user->id,"type"=>"Cancelation","data_x"=>"cancel_credits"]);
        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);

    }

    public function ajaxGetClassMedlin(Request $request){
        $user = User::getCurrent();
        $date_time =   $request->input('datetime');
        if( strpos( $date_time, ',' ) !== false ){
            $date_time =  strtok($date_time, ',');
        }
        $s = strtotime($date_time);
        $onlydate = date('Y-m-d', $s);
        $oneplus_date = date('Y-m-d', strtotime($date_time . ' +1 day'));

        $sheduled_classes = DB::table('classes')->where('user_id', $user->id)->where('class_time', '>=', $onlydate)->where('class_time', '<', $oneplus_date)->where('location_id', 1)->get();
        $i = 0;
        foreach ($sheduled_classes as $row){
            $id = $row->id;
            $i = $i+1;
        }
        echo  $i;
    }

    public function updateDeleTrialDate(){
        $user = User::getCurrent();

        $user_subscription=$user->getCurrentSubscription();

        try {
            $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
        } catch (\Exception $e){
            Log::error('Error cancel subscription (updateDeleTrialDate): '.$user->email);
        }

        if($user->active_dele_trial){
            return redirect()->route("billing")->withErrors(["You can only get the DELE Trial once, we invite you to change to the DELE plan if you wish."]);
        }

        $active_dele_trial = new ActiveDeleTrial();
        $active_dele_trial->user_id = $user->id;
        $active_dele_trial->activation_day = $user_subscription->ends_at;
        $active_dele_trial->save();

        return redirect()->route("billing")->with(["message_info"=>"Your operation has been successfully processed"]);
    }

    public function updateSubscription(Request $request){

        $user = User::getCurrent();
        Log::info("User: ".($user?$user->email:"No User")." Updating Plan");
        $subscription=$request->get("subscription");
        $test_instant=$request->get("instant");
        Log::info("Instant: ".$test_instant);
        //baselang_dele_test
        if($subscription=="baselang_dele_test"){

            $user_subscription=$user->getCurrentSubscription();

            if($user_subscription && ($user_subscription->status=="active" || $user_subscription->status=="future"))
            {
                try {
                    $result = \chargebee_Transaction::sale([
                        'amount' => '1.00',
                        'paymentMethodToken' => $user->payment_method_token,
                        'options' => [
                            'submitForSettlement' => True,
                        ]
                    ]);

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    \Log::info("Manual Add DELE Trial ".$user->email." ".$user->id);
                }catch (\Exception $e){
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

                try{
                    if($user_subscription->status=="active") {
                        $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->ends_at)->add(new \DateInterval("P7D"));
                    }else{
                        $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->starts_at)->add(new \DateInterval("P7D"));
                    }

                    $result = \ChargeBee_Subscription::create([
                        'paymentMethodToken' => $user->payment_method_token,
                        'planId' => $user_subscription->plan,
                        'firstBillingDate' => $start_date,
                    ]);

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    if($result->success){
                        Subscription::where("user_id",$user->id)->delete();
                        Subscription::create(["status"=>"active","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan"=>$subscription,"starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")]);

                        $dele_trial_test = new DeleTrialTest();
                        $dele_trial_test->user_id = $user->id;
                        $dele_trial_test->completed = 0;
                        $dele_trial_test->ends_at_last_subscription = $user_subscription->ends_at;
                        $dele_trial_test->save();

                        $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                        $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

                        /*
                        foreach($classes as $key => $class){
                            $class->removeZoom();
                            $class->delete();
                        }*/
                    }

                    \Log::info("Manual Add DELE Trial (With Subscription) ".$user->email." ".$user->id);
                }catch (\Exception $e){
                    Log::error('Error: '.var_export($e->getMessage(),true));
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

                try{
                    Subscription::where("id",$user_subscription->id)->delete();
                    $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                }catch (\Exception $e){
                    Log::error('Error Deleting Subscription ID: '.var_export($e->getMessage(),true));
                }
            }else{

                try {
                    $result = \ChargeBee_Transaction::createAuthorization([
                        'amount' => '1.00',
                        'paymentMethodToken' => $user->payment_method_token,
                        'options' => [
                            'submitForSettlement' => True,
                        ]
                    ]);

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    if($result->success){
                        Subscription::where("user_id",$user->id)->delete();
                        Subscription::create(["status"=>"active","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan"=>$subscription,"starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")]);

                        $dele_trial_test = new DeleTrialTest();
                        $dele_trial_test->user_id = $user->id;
                        $dele_trial_test->completed = 0;

                        if($user_subscription) {
                            $dele_trial_test->ends_at_last_subscription = $user_subscription->ends_at;
                        }else{
                            $dele_trial_test->ends_at_last_subscription = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d");
                        }

                        $dele_trial_test->save();

                        $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                        $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

                        /*foreach($classes as $key => $class){
                            $class->removeZoom();
                            $class->delete();
                        }*/
                    }

                    Log::info("Manual Add DELE Trial (No Subscription) ".$user->email." ".$user->id);
                }catch (\Exception $e){
                    Log::error('Error: '.var_export($e->getMessage(),true));
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

            }
        }
        else{
            if($test_instant!=null && $subscription!="baselang_hourly")
            {
                $instant=$request->get("instant")==1?true:false;
            }
            elseif($subscription=="baselang_hourly") {
                $instant=false;
            } else {
                $instant=true;
            }

            if(!$subscription){
                $subscription="baselang_149";
                if($user->last_unlimited_subscription=="baselang_99"){
                    $subscription="baselang_99";
                }
                if($user->last_unlimited_subscription=="baselang_129"){
                    $subscription="baselang_129";
                }
            }

            if(!$user->subscribed()){
                try {
                    $result = \ChargeBee_Subscription::create([
                        'paymentMethodToken' => $user->payment_method_token,
                        'planId' => $subscription
                    ]);

                    if($result->success) {
                        if(in_array($subscription,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])) {
                            Log::info("New plan in-person (".$subscription.")");
                            //BuyPrebook::where("user_id",$user->id)->where("type","silver")->where("status",1)->delete();

                            try {
                                if(\App::environment('production')) {
                                    //Email to Niall and Thomas
                                    \Mail::send('emails.new_user_location', ["user" => $user, "subscription" => $subscription], function ($message) use ($user, $subscription) {
                                        $message->subject("New student - ".$subscription);
                                        $message->bcc(['info@buscatupaz.com' => 'Niall', 'carlosdevia@imaginacolombia.com' => 'Carlos Devia']);
                                    });
                                }
                            } catch (\Exception $e) {
                                Log::error('Cant send email: '.$e->getMessage());
                            }

                        }
                    }

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                    Log::info("Subscription Done 123: ".$user->email." Subscription: ".$subscription." Result: ".var_export($result->subscription->id,true));

                    //User::where("id",$user->id)->update(["credits"=>0]);
                    Subscription::where("user_id",$user->id)->delete();
                } catch (\Exception $e){
                    Log::error("Subscription Error: ".$user->email." Subscription: ".$subscription." Result: ".var_export($e->getMessage(),true));
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

            } else {

                $user_subscription=$user->getCurrentSubscription();
                Log::info($user_subscription);

                if($subscription=="baselang_99"){
                    if($user->last_unlimited_subscription!="baselang_99" || $user_subscription->plan->name!="baselang_99"){
                        $subscription="baselang_149";
                    }
                }

                if($subscription=="baselang_129"){
                    if($user->last_unlimited_subscription!="baselang_129" || $user_subscription->plan->name!="baselang_129"){
                        $subscription="baselang_149";
                    }
                }

                if(!$instant){
                    if($user_subscription->plan->name=="baselang_hourly"){
                        $instant=true;
                    }
                }

                if($instant){
                    if($user_subscription->plan->name=="baselang_dele_realworld" || $user_subscription->plan->name=="baselang_dele_realworld_trial"){
                        $instant=false;
                    }
                }

                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$user_subscription->plan->name]);

                $plans=Subscription::getPlans();
                //$plans=Plan::get();

                Log::info("Pro-rate ## ".$subscription." US: ".$user_subscription->plan->name." instant: ".$instant);
                //Prorate
                if($user_subscription->plan->name!="baselang_hourly" && (!in_array($user_subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) || (in_array($user_subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) && in_array($subscription,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]))) && $instant)
                {
                    
                    Log::info("Pro-rate ".$subscription." US: ".$user_subscription->plan->name);
                    try
                    {  
                        if($plans[$subscription][1]>$plans[$user_subscription->plan->name][1]){
                            //charge on change pending
                            $changes=['price'=>$plans[$subscription][1], 'planId'=>$subscription, 'options'=>['prorateCharges'=>true]];
                            $result=\ChargeBee_Subscription::update($user_subscription->subscription_id, $changes);
                            $user->last_unlimited_subscription=$subscription;
                            User::where("id",$user->id)->update(["last_unlimited_subscription"=>$subscription]);

                            if($result->success) {
                                if(!in_array($user_subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) && in_array($subscription,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) && !$user->location_id) {
                                    Log::info("Change of plan online (".$user_subscription->plan->name.") to plan in-person (".$subscription.")");
                                    //BuyPrebook::where("user_id",$user->id)->where("type","silver")->where("status",1)->delete();

                                    try {
                                        if(\App::environment('production')) {
                                            //Email to Niall and Thomas
                                            \Mail::send('emails.new_user_location', ["user" => $user, "subscription" => $subscription], function ($message) use ($user, $subscription) {
                                                $message->subject("New student - ".$subscription);
                                                $message->bcc(['info@buscatupaz.com' => 'Niall', 'carlosdevia@imaginacolombia.com' => 'Carlos Devia']);
                                            });
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('Cant send email: '.$e->getMessage());
                                    }

                                    if($user->active_locations){
                                        $user->active_locations->delete();
                                    }

                                }
                            }

                            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                        } else {
                            try
                            {
                                Subscription::where("id",$user_subscription->id)->delete();
                                $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                            }
                            catch (\Exception $e)
                            {
                                Log::error('Error Deleting Subscription ID: '.var_export($e->getMessage(),true));
                            }

                            try {
                                $result = \ChargeBee_Subscription::create([
                                    'paymentMethodToken' => $user->payment_method_token,
                                    'planId' => $subscription,
                                ]);
                                $user->last_unlimited_subscription=$subscription;
                                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$subscription]);

                                if($result->success) {
                                    if(in_array($user_subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) && !in_array($subscription,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) && $user->location_id) {
                                        Log::info("Change of plan in person (".$user_subscription->plan->name.") to plan the line (".$subscription.")");
                                        $user->location_id=null;
                                        User::where("id",$user->id)->update(["location_id"=>null]);
                                        //BuyPrebook::where("user_id",$user->id)->where("type","silver")->where("status",1)->delete();
                                    }
                                }

                                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                            }
                            catch (\Exception $e)
                            {
                                return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                            }

                        }

                    }
                    catch (\Exception $e)
                    {
                        Log::error('Error Updating Subscription ID: '.var_export($e->getMessage(),true));
                    }
                }
                //No prorate 
                else
                {
                    try
                    {
                        Subscription::where("id",$user_subscription->id)->delete();
                        $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                        if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                    }
                    catch (\Exception $e)
                    {
                        Log::error('Error Deleting Subscription ID: '.var_export($e->getMessage(),true));
                    }

                    if($user_subscription->plan->name=="baselang_hourly") {
                        try
                        {
                            $result = \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
                                "planId" => $subscription
                            ]); 
                            $user->last_unlimited_subscription=$subscription;
                            User::where("id",$user->id)->update(["last_unlimited_subscription"=>$subscription]);

                            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                            Log::info("Subscription Done From baselang_hourly: ".$user->email." Subscription: ".$subscription." Result: ".var_export($result,true));
                        }
                        catch (\Exception $e)
                        {
                            Log::error("Subscription Error From baselang_hourly: ".$user->email." Subscription: ".$subscription." Result: ".var_export($e->getMessage(),true));
                            return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                        }

                    }else {

                        try
                        {
                            if($user_subscription->status=="future")
                            {
                                $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->starts_at);
                            }
                            else
                            {
                                $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->ends_at);
                            }

                            $result = \ChargeBee_Subscription::create([
                                'paymentMethodToken' => $user->payment_method_token,
                                'planId' => $subscription,
                                'firstBillingDate' => $start_date,
                            ]);

                            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                            Log::info("Subscription Done 456: ".$user->email." Subscription: ".$subscription." Result: ".var_export($result,true));
                        }
                        catch (\Exception $e)
                        {
                            Log::error("Subscription Error: ".$user->email." Subscription: ".$subscription." Result: ".var_export($e->getMessage(),true));
                            return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                        }

                    }

                }

                if(($user_subscription->plan->name=="baselang_dele" && ($subscription=="baselang_129" || $subscription=="baselang_149")) || (($user_subscription->plan->name=="baselang_129" || $user_subscription->plan->name=="baselang_149") && $subscription=="baselang_dele")) {
                    $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                    $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

                    /*foreach($classes as $key => $class){
                        $class->removeZoom();
                        $class->delete();
                    }*/
                }

            }
        }

        if($user && $user->pause_account){
            $user->pause_account->delete();
        }

        if($user->active_dele_trial){
            $user->active_dele_trial->delete();
        }

        if($subscription && in_array($subscription,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
            $location = Location::where("name", "medellin")->first();
            if($location && $location->id!=$user->location_id){
                \Log::info("Location assignment: ".$location->id." - ".$location->name);
                $user->location_id=$location->id;
                User::where("id",$user->id)->update(["location_id"=>$location->id]);
            }
        }

        $user->updateSubscriptionInfo();
        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);

    }

    public function getProgress(){
        $user = User::getCurrent();
        $subscriptionType=session("current_subscription")=="real"?"real":"dele";

        $subscriptionType=session("current_subscription");
        if($subscriptionType=="inmersion") {
            return redirect()->route("dashboard");
        }

        $levels_summary=new \stdClass();
        $statistics=new \stdClass();


        if($subscriptionType=="real"){
            $progressLevel=$user->getProgressLevels($subscriptionType);
            $levels_summary->completed=$progressLevel[1];
            $levels_summary->total=$progressLevel[0];
            $levels=$progressLevel[2];
        } else {
            $levels=new \stdClass();
            $levels_summary->grammar=new \stdClass();
            $levels_summary->skills=new \stdClass();
            $levels_summary->test=new \stdClass();

            $progressLevel=$user->getProgressLevels("grammar");
            $levels_summary->grammar->completed=$progressLevel[1];
            $levels_summary->grammar->total=$progressLevel[0];
            $levels->grammar=$progressLevel[2];

            $progressLevel=$user->getProgressLevels("skills");
            $levels_summary->skills->completed=$progressLevel[1];
            $levels_summary->skills->total=$progressLevel[0];
            $levels->skills=$progressLevel[2];

            $progressLevel=$user->getProgressLevels("test");
            $levels_summary->test->completed=$progressLevel[1];
            $levels_summary->test->total=$progressLevel[0];
            $levels->test=$progressLevel[2];

        }


        $progressLevel=$user->getProgressLevels("elective");
        $levels_summary->completed_elective=$progressLevel[1];
        $levels_summary->total_elective=$progressLevel[0];
        $electives=$progressLevel[2];


        $user_classes=Classes::where("user_id",$user->id)->where("class_time","<=",gmdate("Y-m-d H:i:s"))->where("type",$subscriptionType)->get();
        $statistics->total_classes=$user_classes->count();
        $statistics->user_classes_month=Classes::where("user_id",$user->id)->where("class_time",">=",gmdate("Y-m-")."01 00:00:00")->where("class_time","<=",gmdate("Y-m-d H:i:s"))->where("type",$subscriptionType)->count();
        $statistics->user_classes_week=Classes::where("user_id",$user->id)->where("class_time",">=",\DateTime::createFromFormat("U",strtotime('monday this week'))->format("Y-m-d")." 00:00:00")->where("class_time","<=",gmdate("Y-m-d H:i:s"))->where("type",$subscriptionType)->count();
        $statistics->user_level_month=$user->levelPrgress();

        $teachers=[];
        foreach($user_classes->groupBy("teacher_id") as $k=>$teacher){
            $teachers[$k]=$teacher->count();
        };
        arsort($teachers);
        $teachers=array_slice($teachers,0,6,true);
        foreach($teachers as $k=>&$teacher){
            $total=$teacher;
            $teacher=User::where("id",$k)->first();
            $teacher->total_classes=$total;
        };

        return view("user.progress",["level_progress"=>$user->getProgress(),"menu_active"=>"progress","levels"=>$levels,"electives"=>$electives,"levels_summary"=>$levels_summary,"statistics"=>$statistics,"teachers"=>$teachers]);
    }

    public function unlinkGoogleAccount(){
        $user = User::getCurrent();

        if(!$user->getGoogleToken()){
            return redirect()->route("profile")->with(["message_info"=>"Your Google Account has been removed"]);
        }

        try {
            $googleClient=GoogleClient::getGoogleClient();
            $googleClient->setAccessToken($user->getGoogleToken());
            $googleClient->revokeToken();

            $user->google_token=null;
            User::where("id",$user->id)->update(["google_token"=>null,"refresh_google_token"=>null]);
        } catch (Exception $e) {
            return redirect()->route("profile")->withErrors(["It was not possible to remove your Google Account"]);
        }

        return redirect()->route("profile")->with(["message_info"=>"Your Google Account has been removed"]);
    }

    public function linkGoogleAccount(Request $request){
        $user = User::getCurrent();

        $code = $request->only(array('code'));
        $googleClient=GoogleClient::getGoogleClient();

        try {

            $user->google_token=json_encode($googleClient->authenticate($code["code"]));
            if($user->google_token){
                User::where("id",$user->id)->update(["google_token"=>json_encode($googleClient->getAccessToken())]);
            }

            if($googleClient->getRefreshToken()!=NULL){
                User::where("id",$user->id)->update(["refresh_google_token"=>$googleClient->getRefreshToken()]);
            }

        } catch (Exception $e) {
            return redirect()->route("profile")->withErrors(["Error updating your profile details."]);
        }

        return redirect()->route("profile")->with(["message_info"=>"Your Google Account has been added"]);
    }

    public function connectGoogleAccount(){
        $googleClient=GoogleClient::getGoogleClient();
        return redirect()->to($googleClient->createAuthUrl());
    }


    public function saveUserSignup(Request $request){

        $this->validate(request(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);


        $data=($request->only(["first_name","last_name","email","zoom_email","timezone","description","create_provider","company","password"]));
        $data=array_filter($data);
        $data["password"]=Hash::make($data["password"]);

        $check_user=User::where("email",$data["email"])->first();
        if($check_user){
            return redirect()->back()->withErrors(["Este e-mail (".$data["email"].") ya esta registrado con nosotros"]);
        }

        //if(!isset($data["activated"]) || !$data["activated"]){
        //    $data["activated"]=0;
        //} else {
            $data["activated"]=1;
        //}

        $user=User::create($data);


        \Log::info("User: " .$data["email"]. " Created By himself.");

        if(!$user->activated){
            $user->activated=1;
            User::where("id",$user->id)->update(["activated"=>1]);
        }

        try {
            auth()->login($user);
        } catch (\Exception $e) {
            Log::error('Can\'t auto-login the user: '.$e->getMessage());
        }


        try {


            if(\App::environment('production')){
                \Mail::send('emails.user_welcome', ["user" => $user], function ($message) use ($user) {
                    $message->subject(__("Welcome to Buscatupaz")."!");
                    $message->to($user->email, $user->first_name);
                });
            }
        } catch (\Exception $e) {
            Log::error('Can\'t send email: '.$e->getMessage());
        }


        $user->detachRoles($user->roles);

        if(isset($data["create_provider"]) && $data["create_provider"]==1) {
            $user->attachRole(Role::where("name","teacher")->first());
        } else {
            $user->attachRole(Role::where("name","student")->first());
        }


        $delete_file=$request->input("delete-photo");

        try {
            User::where("id",$user->id)->update($data);

            if($delete_file){
                if(file_exists(public_path()."/assets/users/photos/".$user->id.".jpg")){
                    Storage::disk("uploads")->delete("/assets/users/photos/".$user->id.".jpg");
                }
            } else {
                //upload file
                if($request->file('photo')){
                    $profile_image = Storage::disk("uploads")->putFileAs('/assets/users/photos', $request->file('photo'),$user->id.".jpg");
                    $request->photo->storeAs('assets/users/photos',$user->id.'.jpg','uploads');
                }
            }


        } catch (\Exception $e){
            return redirect()->route("dashboard")->withErrors(["The email ".$data["email"]." already exists."]);
        }

        return redirect()->route("dashboard")->with(["message_info"=>__("Account Registered")]);
    }



    public function saveProfile(Request $request){

        $user = User::getCurrent();
        $data=$request->only(["first_name","last_name","email","zoom_email","timezone","youtube_url","description","company"]);

        if(empty($data["timezone"])) {
            $data["timezone"] = $user->timezone;
        }

        $delete_file=$request->input("delete-photo");
        $password=$request->input("password");

        try {
            User::where("id",$user->id)->update($data);
            $edit_user=User::where("id",$user->id)->first();


            $user_calendar=$request->get("user_calendar");
            if($user_calendar){
                UserCalendar::where("user_id",$edit_user->id)->delete();
                foreach($user_calendar as $j=>$user_interval_day){
                    foreach ($user_interval_day["from"] as $k=>$user_interval){
                        $from=\DateTime::createFromFormat("H:i",$user_interval,new \DateTimeZone($edit_user->timezone));
                        $from = Classes::fixTime($from);
                        if($from){
                            $start_from=$from->format("j");
                            $from=$from->setTimezone(new \DateTimeZone("UTC"));
                            if(!\DateTime::createFromFormat("H:i",$user_interval_day["till"][$k],new \DateTimeZone($edit_user->timezone))){
                                continue;
                            };


                            $till=\DateTime::createFromFormat("H:i",$user_interval_day["till"][$k],new \DateTimeZone($edit_user->timezone))->setTimezone(new \DateTimeZone("UTC"));
                            $till = Classes::fixTime($till);
                            $till = $till->format("H:i:s");

                            $day=$j;
                            if($start_from!=$from->format("j")){
                                $day++;
                                if($day==8){
                                    $day=1;
                                }
                            }

                            $calendar_data=["from"=>$from->format("H:i:s"),"till"=>$till,"day"=>$day,"user_id"=>$edit_user->id];

                            UserCalendar::create($calendar_data);
                        }

                    }
                }
            }


            if($delete_file){
                if(file_exists(public_path()."/assets/users/photos/".$user->id.".jpg")){
                    Storage::disk("uploads")->delete("/assets/users/photos/".$user->id.".jpg");
                }
            } else {
                //upload file
                if($request->file('photo')){
                    $profile_image = Storage::disk("uploads")->putFileAs('/assets/users/photos', $request->file('photo'),$user->id.".jpg");
                    $request->photo->storeAs('assets/users/photos',$user->id.'.jpg','uploads');
                }
            }

            if(!empty($password)){
                if(strlen($password)<5){
                    throw new \Exception("Your Password Must Be at Least 5 Characters");
                }

                User::where("id",$user->id)->update(["password"=>Hash::make($password)]);
            }


        } catch (\Exception $e){
            return redirect()->route("profile")->withErrors(["The email ".$data["email"]." already exists."]);
        }

        return redirect()->route("profile")->with(["message_info"=>__("Your information has been updated")]);
    }

    public function getProfile(){
        $user = User::getCurrent();

        $google_email=false;
        $google_info=$user->checkGoogleToken();

        if($google_info){
            $google_email=$google_info->email;
        }

        return view("user.profile",["google_email"=>$google_email]);
    }

    public function getRegister(){
        $user = User::getCurrent();

        if($user){
            return redirect()->route("dashboard");
        }

        return redirect()->to("https://portal.buscatupaz.com/signup/user");
    }

    public function stopImpersonate()
    {
        $impersontate_by=session("impersonated_by");
        if($impersontate_by){
            \Session::flush();
            Auth::loginUsingId($impersontate_by, true);
        }

        return redirect()->route("home");
    }

    public function postLogin(){

        $data = Input::only('email','password');
        $check_user=User::where("email",$data['email'])->first();

        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true)) {
            $user = User::getCurrent();
            \Log::info('Login: '.$user->id." - ".$user->email);
            if(!$user->activated){
                Auth::logout();
                return redirect()->route("login")->withErrors(["Your user account has been disabled."]);
            }
            User::where("id",$user->id)->update(["last_login"=>date('Y-m-d H:i:s')]);
            $user->verifyRole();
            $user->updateSubscriptionInfo();
            return redirect()->route("dashboard");
        }

        \Log::error('Fail Login: '.$data["email"]);
        if(!$check_user){
            return redirect()->back()->withErrors(["The email you entered is incorrect"]);
        }else{
            return redirect()->back()->withErrors(["The password you entered is incorrect"]);
        }
    }

    public function sendResetLinkEmail(Request $request){
        $email=$request->get("email");

        $user=User::where("email",$email)->first();
        if($user){
            $user_token = new TokenReset();
            $user_token->email=$user->email;
            $user_token->save();

            $resetLink=route("password_reset_token",["token"=>$user_token->token]);
            try {
                if(\App::environment('production')){
                    \Mail::send('emails.user_reset_password', ["user"=>$user,"resetLink"=>$resetLink], function($message) use($user)
                    {
                        $message->subject("Reset BaseLang password");
                        $message->to($user->email,$user->first_name);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Cant send email: '.$e->getMessage());
            }

        }

        return redirect()->route("login")->withErrors(["An email has been sent to you with a link which you may use to reset your password."]);
    }

    public function getCreditsBuy(){
        $user = User::getCurrent();

        if(!$user->getCurrentSubscription() || $user->getCurrentSubscription()->plan->name!="baselang_hourly"){
            return redirect()->route("profile");
        }

        return view("user.credits",["menu_active"=>"credits"]);
    }

    public function resetPassword(Request $request){
        $token=$request->get("token");
        $password=$request->get("password");

        if(!$password || strlen($password)<5){
            return redirect()->back()->withErrors(["The minimum password length is five characters."]);
        }

        $token=TokenReset::where("token",$token)->first();
        $limitTime=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"))->sub(new \DateInterval("PT1H"));


        if(!$token || $token->created_at<$limitTime->format("Y-m-d H:i:s")){
            return redirect()->route("login")->withErrors(["Your authorization token has expired."]);
        }

        $user=User::where("email",$token->email)->first();
        if(!$user){
            return redirect()->route("login")->withErrors(["Your authorization token has expired."]);
        }


        User::where("id",$user->id)->update(["password"=>Hash::make($password)]);
        TokenReset::where("token",$token)->delete();
        return redirect()->route("login")->withErrors(["Your password has been successfully changed."]);
    }

    public function getResetPasswordToken($token){
        $user = User::getCurrent();

        if($user){
            return redirect()->route("dashboard");
        }

        $token=TokenReset::where("token",$token)->first();
        $limitTime=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"))->sub(new \DateInterval("PT1H"));

        if(!$token || $token->created_at<$limitTime->format("Y-m-d H:i:s")){
            return redirect()->route("login")->withErrors(["Your authorization token has expired. "]);
        }

        return view("user.reset_password_token",["token"=>$token->token]);
    }

    public function getResetPassword(){
        $user = User::getCurrent();

        if($user){
            return redirect()->route("dashboard");
        }

        return view("user.reset_password");
    }

    public function getLogin(){

        $user = User::getCurrent();

        if($user){
            return redirect()->route("dashboard");
        }

        return view("user.login");
    }

    public function submitSubscription(){

    }

    public function changeSubscription(){
        Cache::flush();
        $user = User::getCurrent();
        $plans=Subscription::getPlans();

        if(!$user){
            return redirect()->route("login")->with(["message_info"=>"Your session has expired"]);
        }

        $user->subscription=$user->getCurrentSubscription();
        $user->last_active=$user->getLastActive();
        $user->updatePayMethod();
        if(!$user->paypal_email && !$user->card_last_four){
            return redirect()->route("change_card")->withErrors(["We need this to update your subscription."]);
        }

        $prorated_amount_one = false;
        $prorated_amount_two = false;

        if($user->subscription){
            if($user->subscription->plan=="baselang_129" || $user->subscription->plan=="baselang_149"){
                //To DELE

                if($user->subscription->plan=="baselang_129" ) {
                    $difference_amount = $plans["baselang_dele"][1]-$plans["baselang_129"][1];
                }else {
                    $difference_amount = $plans["baselang_dele"][1]-$plans["baselang_149"][1];
                }

                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->starts_at);
                $date_two = new \DateTime(gmdate("Y-m-d"));
                $diff = $date_one->diff($date_two);
                $days = $diff->days;

                if($days==0){
                    $days=1;
                }
                $prorated_amount_one = $difference_amount-($daily_amount*$days);
                $prorated_amount_one = round($prorated_amount_one,2);

                //To DELE + Real World
                if($user->subscription->plan=="baselang_129" ) {
                    $difference_amount = $plans["baselang_dele_realworld"][1]-$plans["baselang_129"][1];
                }else {
                    $difference_amount = $plans["baselang_dele_realworld"][1]-$plans["baselang_149"][1];
                }

                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->starts_at);
                $date_two = new \DateTime(gmdate("Y-m-d"));
                $diff = $date_one->diff($date_two);
                $days = $diff->days;

                if($days==0){
                    $days=1;
                }
                $prorated_amount_two = $difference_amount-($daily_amount*$days);
                $prorated_amount_two = round($prorated_amount_two,2);
            }

            if($user->subscription->plan=="baselang_dele"){
                //To DELE + Real World
                $difference_amount = $plans["baselang_dele_realworld"][1]-$plans["baselang_dele"][1];
                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->starts_at);
                $date_two = new \DateTime(gmdate("Y-m-d"));
                $diff = $date_one->diff($date_two);
                $days = $diff->days;

                if($days==0){
                    $days=1;
                }
                $prorated_amount_two = $difference_amount-($daily_amount*$days);
                $prorated_amount_two = round($prorated_amount_two,2);
            }
        }

        $is_dele_trial=false;
        $customer = \ChargeBee_Customer::retrieve($user->chargebee_id);
        if($customer) {
//            foreach($customer->paymentMethods as $payment_method) {
//                foreach($payment_method->subscriptions as $subscription) {
//                    if($subscription->planId=="baselang_dele") {
//                        $is_dele_trial=true;
//                    }
//                }
//            }
        }

        if($user->dele_trial_test) {
            $is_dele_trial=true;
        }

        return view("user.change_subscription",["menu_active"=>"billing","user"=>$user,"breadcrumb"=>true,"plans"=>$plans,"prorated_amount_one"=>$prorated_amount_one,"prorated_amount_two"=>$prorated_amount_two,"is_dele_trial"=>$is_dele_trial]);
    }

    public function changeLocation(){
        Cache::flush();
        $user = User::getCurrent();
        $plans=Subscription::getPlans();

        if(!$user){
            return redirect()->route("login")->with(["message_info"=>"Your session has expired"]);
        }

        if($user->check_landing_date){
            return redirect()->route("billing")->withErrors(["message_info"=>"You still can not change location!"]);
        }

        $user->subscription=$user->getCurrentSubscription();
        $user->last_active=$user->getLastActive();
        $user->updatePayMethod();
        if(!$user->paypal_email && !$user->card_last_four){
            return redirect()->route("change_card")->withErrors(["We need this to update your subscription."]);
        }

        $prorated_amount_one = false;
        $prorated_amount_two = false;

        if($user->subscription){
            if($user->subscription->plan=="baselang_129"){
                //To DELE
                $difference_amount = $plans["baselang_dele"][1]-$plans["baselang_129"][1];
                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->starts_at);
                $date_two = new \DateTime(gmdate("Y-m-d"));
                $diff = $date_one->diff($date_two);
                $days = $diff->days;

                if($days==0){
                    $days=1;
                }
                $prorated_amount_one = $difference_amount-($daily_amount*$days);
                $prorated_amount_one = round($prorated_amount_one,2);

                //To DELE + Real World
                $difference_amount = $plans["baselang_dele_realworld"][1]-$plans["baselang_129"][1];
                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->starts_at);
                $date_two = new \DateTime(gmdate("Y-m-d"));
                $diff = $date_one->diff($date_two);
                $days = $diff->days;

                if($days==0){
                    $days=1;
                }
                $prorated_amount_two = $difference_amount-($daily_amount*$days);
                $prorated_amount_two = round($prorated_amount_two,2);
            }

            if($user->subscription->plan=="baselang_dele"){
                //To DELE + Real World
                $difference_amount = $plans["baselang_dele_realworld"][1]-$plans["baselang_dele"][1];
                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->starts_at);
                $date_two = new \DateTime(gmdate("Y-m-d"));
                $diff = $date_one->diff($date_two);
                $days = $diff->days;

                if($days==0){
                    $days=1;
                }
                $prorated_amount_two = $difference_amount-($daily_amount*$days);
                $prorated_amount_two = round($prorated_amount_two,2);
            }
        }

        $is_dele_trial=false;
        $customer = \ChargeBee_Customer::retrieve($user->chargebee_id);
//        if($customer) {
//            foreach($customer->paymentMethods as $payment_method) {
//                foreach($payment_method->subscriptions as $subscription) {
//                    if($subscription->planId=="baselang_dele") {
//                        $is_dele_trial=true;
//                    }
//                }
//            }
//        }

        if($user->dele_trial_test) {
            $is_dele_trial=true;
        }

        return view("user.change_location",["menu_active"=>"billing","user"=>$user,"breadcrumb"=>true,"plans"=>$plans,"prorated_amount_one"=>$prorated_amount_one,"prorated_amount_two"=>$prorated_amount_two,"is_dele_trial"=>$is_dele_trial]);
    }

    public function getChangeLocation($preview=null){
        $user = User::getCurrent();
        $plans=Plan::where('location_name','<>',$user->subscription->plan->location->name)->orderBy('price','asc')->where('status',1)->get();
        $locations = Location::where('name','<>',$user->subscription->plan->location->name)->get();

        return view("user.change_subscription",["menu_active"=>"billing","breadcrumb"=>true,"plans"=>$plans,"locations"=>$locations->pluck('display_name')->toArray(),"preview"=>$preview]);
    }

    public function updateCardChargebee(Request $request){
        $user = User::getCurrent();

        $refresh_chargebee = $user->updateChargebeeInfo();
        Log::info("Refresh Chargebee: ".$refresh_chargebee);

        $payment_method_nonce = $request->get("payment_method_nonce");

        try {
            $result = \Chargebee_PaymentMethod::create([
                'customerId' => $user->chargebee_id,
                'paymentMethodNonce' => $payment_method_nonce,
                'options' => [
                    'makeDefault' => true,
                    'verifyCard' => true
                ]
            ]);


            if(isset($result->errors)) {
                Log::error('Error Payment Method: '.var_export($result,true));
                throw new \Exception("Payment Method not Validated.");
            }

            Log::info("Create payment method - User: ".$user->id." - payment_method_nonce: ".$payment_method_nonce);
            Log::info("Method Created: ".var_export($result->paymentMethod->token,true));
        } catch (\Exception $e){
            Log::error('Error Payment Method: '.var_export($e->getMessage(),true));
            return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
        }

        $customer = \ChargeBee_Customer::retrieve($user->chargebee_id);
        if($customer && isset($customer->paymentMethods)) {

            foreach($customer->paymentMethods as $paymentMethod) {
                if($paymentMethod->default) {
                    if (isset($paymentMethod->last4)) {
                        User::where("id", $user->id)->update(['paypal_email' => null, 'card_last_four' => $paymentMethod->last4, 'payment_method_token' => $paymentMethod->token, 'pay_image' => $paymentMethod->imageUrl]);
                    } else {
                        User::where("id", $user->id)->update(['paypal_email' => $paymentMethod->email, 'card_last_four' => null, 'payment_method_token' => $paymentMethod->token, 'pay_image' => $paymentMethod->imageUrl]);
                    }
                }

                foreach($paymentMethod->subscriptions as $subscription) {

                    if($subscription->status==\Chargebee_Subscription::ACTIVE || $subscription->status==\Chargebee_Subscription::PENDING){
                        try {
                            Log::info('Try to update Payment Method: '.$result->paymentMethod->token.' In: '.$subscription->id.' For: '. $user->email);
                            $changes=['paymentMethodToken'=>$result->paymentMethod->token];
                            $new_result=\Chargebee_Subscription::update($subscription->id, $changes);
                            if(isset($new_result->success)) {
                                Log::info('Successful update of the subscription payment method - New token: '.$result->paymentMethod->token);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error Updating Subscription ID: '.var_export($e->getMessage(),true));
                        }
                    }
                }
            }
        }

        return redirect()->route("billing")->with(["message_info"=>"Your Payment Method has been updated"]);
    }

    public function chargebeeSession(){
        try {
            $user = User::getCurrent();
            $result = \ChargeBee_PortalSession::create([
                "customer" => ["id" => $user->chargebee_id]
            ]);
            $response = $result->portalSession()->getValues();
        } catch (\Exception $e){
            Error::reportError('Error chargebee session',$e->getLine(),$e->getMessage());
            return response()->json();
        }
        return response()->json($response);
    }

    public function getChangeCard(){
        Cache::flush();
        $user = User::getCurrent();
        return view("user.change_card",["menu_active"=>"billing","user"=>$user,"breadcrumb"=>true]);
    }

    public function resubscribe(){
        $user = User::getCurrent();
        if($user->subscribed()){
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        }

        if($user->check_landing_date){
            return redirect()->route("billing")->withErrors(["message_info"=>"You still can not subscribe with this option of our platform!"]);
        }

        $last_subscription='';
        try {
            $last_subscription=$user->subscriptions()->orderBy("ends_at","desc")->first();
            $start_date=false;
            if(!$last_subscription){
                $last_subscription=$user->last_unlimited_subscription;
                if(!$last_subscription){
                    $last_subscription="baselang_149";
                }
            } else {
                if($last_subscription->ends_at>gmdate("Y-m-d")){
                    $start_date=\DateTime::createFromFormat("Y-m-d",$last_subscription->ends_at);
                }
                $last_subscription=$last_subscription->plan;
            }
            if(!$last_subscription){
                $last_subscription="baselang_149";
            }

            if($last_subscription=="baselang_149_trial") {
                $last_subscription="baselang_149";
                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$last_subscription]);
                $user->last_unlimited_subscription=$last_subscription;
                \Log::info($user->email." - From baselang_149_trial to baselang_149 in resubscribe");
            }
            elseif($last_subscription=="baselang_129_trial" || $last_subscription=="baselang_129") {
                $last_subscription="baselang_149";
                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$last_subscription]);
                $user->last_unlimited_subscription=$last_subscription;
                \Log::info($user->email." - From baselang_129_trial or baselang_129 to baselang_149 in resubscribe");
            }
            elseif($last_subscription=="baselang_99_trial" || $last_subscription=="baselang_99") {
                $last_subscription="baselang_149";
                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$last_subscription]);
                $user->last_unlimited_subscription=$last_subscription;
                \Log::info($user->email." - From baselang_99_trial or baselang_99 to baselang_149 in resubscribe");
            }
            elseif($last_subscription=="baselang_dele_trial") {
                $last_subscription="baselang_dele";
                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$last_subscription]);
                $user->last_unlimited_subscription=$last_subscription;
                \Log::info($user->email." - From baselang_dele_trial to baselang_dele in resubscribe");
            }
            elseif($last_subscription=="baselang_dele_test") {
                $last_subscription="baselang_dele";
                User::where("id",$user->id)->update(["last_unlimited_subscription"=>$last_subscription]);
                $user->last_unlimited_subscription=$last_subscription;
                \Log::info($user->email." - From baselang_dele_test to baselang_dele in resubscribe");
            }

            if($start_date && $last_subscription!="baselang_hourly"){
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $last_subscription,
                    'firstBillingDate' => $start_date,
                ]);
            } else {
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $last_subscription
                ]);
            }

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

            Subscription::where("user_id",$user->id)->delete();

        } catch (\Exception $e){
            Log::error('Error Resubscribe Method: '.var_export($e->getMessage(),true).' Last Subscription: '.$last_subscription);
            return redirect()->route("change_card")->withErrors(["We need this to get you re-subscribed."]);
        }

        if($user && $user->pause_account){
            $user->pause_account->delete();
        }

        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
    }

    public function startSubscriptionNow(Request $request) {
        $user = User::getCurrent();

        if(!$user->check_landing_date) {
            return redirect()->route("billing")->withErrors(["message_info"=>"You still can not subscribe with this option of our platform!"]);
        }

        $user_subscription = $user->getCurrentSubscription();

        $firstBillingDate = $request->input('firstBillingDate');

        if(isset($firstBillingDate) && $firstBillingDate <= gmdate("Y-m-d")){
            return redirect()->route("billing")->withErrors(["The date must be greater than the current day!"]);
        }

        try {
            Subscription::where("user_id",$user->id)->delete();
            $result = \Chargebee_Subscription::cancel($user_subscription->subscription_id);
            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
        } catch (\Exception $e) {
            Log::error('Error Deleting Subscription ID - Start Subscription Now: '.var_export($e->getMessage(),true));
        }

        try {

            if(!isset($firstBillingDate)) {

                Log::info("No firstBillingDate");
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $user_subscription->plan,
                ]);

            }else {

                Log::info("New firstBillingDate: ".$firstBillingDate);
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $user_subscription->plan,
                    'firstBillingDate' => $firstBillingDate,
                ]);

            }

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            Log::info("Subscription Done From Start Subscription Now: ".$user->email." Subscription: ".$user_subscription->plan." Result: ".var_export($result,true));

            if($result->success && isset($firstBillingDate)) {

                $class_time = \DateTime::createFromFormat("Y-m-d H:i:s",$firstBillingDate." 23:59:59");

                $classes = Classes::where("class_time","<=",$class_time)->where("user_id",$user->id)->get();
                Log::info("Remove classes prior to this date: ".$firstBillingDate." - Number of classes: ".count($classes));
                Log::info("Classes: ".$classes);

                /*
                foreach($classes as $class){
                    $class->removeZoom();
                    $class->delete();
                }
                */

            }

        } catch (\Exception $e) {
            Log::error("Subscription Error From Start Subscription Now: ".$user->email." Subscription: ".$user_subscription->plan." Result: ".var_export($e->getMessage(),true));
            return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
        }

        return redirect()->route("billing")->with(["message_info"=>"Operation carried out successfully!"]);

    }

    public function getPayment($skip=0){
        $user = User::getCurrent();
        $payment_history=$user->getPaymenthHistory();

//        $i=0;
//        $payments=collect();
//        foreach($payment_history as $payment){
//            $i++;
//
//            if($i<=$skip){
//                continue;
//            };
//
//            $payments->push($payment);
//
//            if($i-$skip==3){
//                break;
//            }
//        }

            // if($i<=$skip){
            //     continue;
            // };

//        $more=count($payments)+$skip!=$payment_history->maximumCount();
        return view("user.includes.paymenth_history",["payments"=>$payment_history]);

    }

    private function getPlan($subscription=false){
        $user = User::getCurrent();
        $plans = Subscription::getPlans();

        if(!$subscription){

            if(!$user->subscribed()){
                return false;
            };
            if(!$user->getCurrentSubscription()){
                return false;
            }
            $subscription=$user->getCurrentSubscription()->plan;
        }

        $plan=new \stdClass();


        if($user->subscribed()){
            $plan->family=$user->getCurrentSubscriptionType();
        } else {
            $subscription=$user->last_unlimited_subscription;
            $plan->family=$user->getLastSubscriptionType();
            if(!$subscription){
                $subscription="baselang_129";
                $plan->family="real";
            }

        }

        $plan->name=$plans[$subscription][0];
        $plan->price=$plans[$subscription][1];
        $plan->features=$plans[$subscription][2];

        try {
            $plan->name=$plans[$subscription][0];
            $plan->price=$plans[$subscription][1];
            $plan->features=$plans[$subscription][2];
        } catch (\Exception $e){
            $plan->name=$plans['baselang_149'][0];
            $plan->price=$plans['baselang_149'][1];
            $plan->features=$plans['baselang_149'][2];
        }

        return $plan;
    }

    public function nowDowngrade(){
        $user = User::getCurrent();
        $user_subscription=$user->getCurrentPendingSubscription();

        $user->last_unlimited_subscription=$user_subscription->plan;
        User::where("id",$user->id)->update(["last_unlimited_subscription"=>$user_subscription->plan]);

        if($user_subscription->plan){
            $location = Location::where("name", "online")->first();
            if($location && $location->id != $user->location_id){
                \Log::info("Location assignment: ".$location->id." - ".$location->name);
                $user->location_id=$location->id;
                User::where("id",$user->id)->update(["location_id"=>$location->id]);
            }

        }

        $user->refreshSubscriptionSession();

        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
    }

    public function cancelNow(){
        $user = User::getCurrent();
        $user_subscription=$user->getCurrentSubscription();

        if($user_subscription->status=="cancelled"){
            Subscription::where("id",$user_subscription->id)->delete();
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been cancelled"]);
        }

        if($user->last_unlimited_subscription=="baselang_99" || $user->last_unlimited_subscription=="baselang_99_trial"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
        }

        if($user->last_unlimited_subscription=="baselang_129" || $user->last_unlimited_subscription=="baselang_129_trial"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
        }

        return redirect()->route("billing");
    }

    public function cancelUndo(){
        $user = User::getCurrent();
        $user_subscription=$user->getCurrentSubscription();

        if($user_subscription->plan=="baselang_dele_test" && !$user->active_dele_trial && $user->dele_trial_test){
            DeleTrialTest::where("id",$user->dele_trial_test->id)->update(["completed"=>1]);
            Subscription::where("id",$user_subscription->id)->delete();
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been cancelled"]);
        }
        elseif($user_subscription->plan=="baselang_dele_test" && $user->active_dele_trial && $user->dele_trial_test){
            DeleTrialTest::where("id",$user->dele_trial_test->id)->update(["completed"=>1]);
            Subscription::where("id",$user_subscription->id)->delete();

            try {
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $user->last_unlimited_subscription,
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                if($user->active_dele_trial){
                    $user->active_dele_trial->delete();
                }

                return redirect()->route("billing")->with(["message_info"=>"Your subscription has been cancelled"]);
            } catch (\Exception $e){
                Log::error("Error on resubscribe: ".var_export($e->getMessage(),true)." Date: ".var_export($start_date,true));
                return redirect()->route("billing")->withErrors(["Error Processing Your request, Try Again."]);
            }
        }

        if($user->pause_account){
            $user->pause_account->delete();
        }

        if($user->active_dele_trial){
            $user->active_dele_trial->delete();
        }

        if($user->last_unlimited_subscription=="baselang_dele_trial"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_dele"]);
            $user->last_unlimited_subscription="baselang_dele";
        }

        if($user->last_unlimited_subscription=="baselang_dele_test"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_dele"]);
            $user->last_unlimited_subscription="baselang_dele";
        }

        if($user->last_unlimited_subscription=="baselang_99_trial"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_99"]);
            $user->last_unlimited_subscription="baselang_99";
        }

        if($user->last_unlimited_subscription=="baselang_129_trial"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_129"]);
            $user->last_unlimited_subscription="baselang_129";
        }

        if($user->last_unlimited_subscription=="baselang_149_trial"){
            User::where("id",$user->id)->update(["last_unlimited_subscription"=>"baselang_149"]);
            $user->last_unlimited_subscription="baselang_149";
        }

        try {
            if($user_subscription->status=="cancelled"){
                if($user_subscription->ends_at==gmdate("Y-m-d")){
                    $user_subscription->ends_at=\DateTime::createFromFormat("Y-m-d",$user_subscription->ends_at)->add(new \DateInterval("P1D"))->format("Y-m-d");
                }
                $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->ends_at);
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $user->last_unlimited_subscription,
                    'firstBillingDate' => $start_date,
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);


                Subscription::where("id",$user_subscription->id)->delete();
                return redirect()->route("billing")->with(["message_info"=>"Your subscription has been cancelled"]);
            }
        } catch (\Exception $e){
            Log::error("Error on resubscribe: ".var_export($e->getMessage(),true)." Date: ".var_export($start_date,true));
            return redirect()->route("billing")->withErrors(["Error Processing Your request, Try Again."]);
        }

        return redirect()->route("billing");
    }

    public function changeType(Request $request){

        $inLesson=false;
        $postMethod=false;
        try {
            $routeName=app('router')->getRoutes()->match(app('request')->create(URL::previous()));
            if(in_array($routeName->action["as"],["lessons","lessons_type","level","lesson"])){
                $inLesson=true;
            };
        } catch (\Exception $e){
            $inLesson=false;
            $postMethod=true;
        }

        if($postMethod){
            return redirect()->route("dashboard");
        }

        $user = User::getCurrent();

        if($user->getCurrentSubscriptionType()=="dele_real" || $user->getCurrentRol()->name=="coordinator"){

            if(session('current_subscription')=="real"){
                session(['current_subscription' => "dele"]);
                if($inLesson){
                    return redirect()->route("lessons")->with(["message_info" => "Switched. You’re now viewing Real World."]);
                }
                return redirect()->back()->with(["message_info"=>"Switched. You’re now viewing DELE."]);

            } else {
                session(['current_subscription' => "real"]);
                if($inLesson) {
                    return redirect()->route("lessons")->with(["message_info" => "Switched. You’re now viewing Real World."]);
                }
                return redirect()->back()->with(["message_info" => "Switched. You’re now viewing Real World."]);
            }
        }



        return redirect()->back();

    }

    public function cancelSubscription(Request $request){
        $user = User::getCurrent();

        $other=$request->get("other");
        $reason=$request->get("reason");

        if($other){
            UserCancellation::create(["user_id"=>$user->id,"other"=>$other,"reason"=>$reason]);
        }
        else {
            UserCancellation::create(["user_id"=>$user->id,"reason"=>$reason]);
        }

        $user_subscription=$user->getCurrentSubscription();

        try {
            $result = \Chargebee_Subscription::cancel($user_subscription->subscription_id);
            Log::info('Canceling Subscription For: '.$user->email.' '.var_export($result->message,true));
            if($result->errors->count()>0) throw new \Exception(isset($result->message)?$result->message:var_export($result->errors->deepAll(),true));

        } catch (\Exception $e){
            Log::error('Error Cancel Subscription: '.var_export($e->getMessage(),true). var_export($user_subscription->subscription_id,true). "For: ".$user->email);
            //return redirect()->route("billing")->withErrors(["Error Processing Your request, Try Again."]);
        }

        if($user_subscription->plan=="baselang_hourly") {
            Subscription::where("id",$user_subscription->id)->delete();
        } elseif($user_subscription->status=="future"){
            $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->starts_at)->sub(new \DateInterval('P30D'));
            Subscription::where("id",$user_subscription->id)->update(["starts_at"=>$start_date->format("Y-m-d"),"ends_at"=>$user_subscription->starts_at,"status"=>"cancelled"]);
        } else {
            Subscription::where("id",$user_subscription->id)->update(["status"=>"cancelled"]);
        }

        $buy_prebook=$user->buy_prebooks()->first();
        if($buy_prebook) {
            BuyPrebook::where("id",$buy_prebook->id)->update(["status"=>0]);
            Prebook::where("user_id",$user->id)->delete();
        }

        if($user->active_locations){
            $user->active_locations->delete();
        }

        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been cancelled"]);
    }

    public function cancelDowngrade(){
        $user = User::getCurrent();
        $user_subscription=$user->getCurrentPendingSubscription();

        if($user_subscription && $user_subscription->status=="future"){

            try {
                Subscription::where("id",$user_subscription->id)->delete();
                $result = \Chargebee_Subscription::cancel($user_subscription->subscription_id);
                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            } catch (\Exception $e){
                Subscription::create($user_subscription->toArray());
                return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
            }

            try {

                $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->starts_at);

                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $user->last_unlimited_subscription,
                    'firstBillingDate' => $start_date,
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            } catch (\Exception $e){
                Subscription::create($user_subscription->toArray());
                return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
            }
        }

        $user->refreshSubscriptionSession();
        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
    }

    public function getConfirmCancelSubscription(Request $request){
        $user = User::getCurrent();

        $reason=$request->get("reason");
        $other=$request->get("other");
        $current_reason=UserCancellation::getReasons()[$reason];
        $user->subscription=$user->getCurrentSubscription();
        $user->plan=$this->getPlan($user->subscription->plan);

        return view("user.cancel_confirm",["menu_active"=>"billing","current_reason"=>$current_reason,"reason"=>$reason,"other"=>$other,"breadcrumb"=>true,"user"=>$user]);
    }

    public function getCancelSubscriptionReason($reason){
        $user = User::getCurrent();

        if(!$user->subscribed()) {
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        } elseif($user->getCurrentSubscription()->status=="cancelled"){
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        }

        $current_reason=UserCancellation::getReasons()[$reason];

        if(strpos($current_reason, "I'll be back") !== false) {
            return redirect()->route("pause_account");
        }

        if(!$reason){
            return redirect()->back();
        }

        return view("user.cancel.".$reason,["menu_active"=>"billing","reason"=>$reason,"current_reason"=>$current_reason,"breadcrumb"=>true]);
    }



    public function getCancelSubscription(){
        $user = User::getCurrent();

        if(!$user){
            return redirect()->route("login")->with(["message_info"=>"Your session has expired"]);
        }

        $user_subscription=$user->getCurrentSubscription();

        if(!$user->subscribed()) {
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        } elseif($user_subscription && $user_subscription->status=="cancelled"){
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        }

        if($user_subscription && ($user_subscription->plan=="baselang_99" || $user_subscription->plan=="baselang_149" || $user_subscription->plan=="baselang_129" || $user_subscription->plan=="baselang_dele" || $user_subscription->plan=="baselang_dele_realworld" || $user_subscription->plan=="baselang_99_trial" || $user_subscription->plan=="baselang_129_trial" || $user_subscription->plan=="baselang_149_trial" || $user_subscription->plan=="baselang_dele_trial" || $user_subscription->plan=="baselang_dele_test" || $user_subscription->plan=="9zhg")){
            return view("user.pause_account",["menu_active"=>"billing", "breadcrumb"=>true, "user_subscription"=>$user_subscription]);
        }

        $reasons=UserCancellation::getReasons();

        return view("user.cancel",["menu_active"=>"billing","breadcrumb"=>true,"reasons"=>$reasons]);
    }

    public function getSurveyPage(){
        $user = User::getCurrent();

        if(!$user){
            return "";
        }

        $user_subscription=$user->getCurrentSubscription();

        if(!$user->subscribed()) {
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        } elseif($user_subscription && $user_subscription->status=="cancelled"){
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        }

        $reasons=UserCancellation::getReasons();

        return view("user.cancel",["menu_active"=>"billing","breadcrumb"=>true,"reasons"=>$reasons]);
    }

    public function getPauseAccount(){
        $user = User::getCurrent();
        $user_subscription=$user->getCurrentSubscription();

        if(gmdate("Y-m-d") < \DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")) {
            return redirect()->route("billing")->with(["message_info"=>"You still can not pause your account, because you are in the trial period!"]);
        }

        if(!$user->subscribed()) {
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        } elseif($user_subscription && $user_subscription->status=="cancelled"){
            return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated"]);
        }

        $user->subscription=$user->getCurrentSubscription();

        return view("user.select_pause_account",["menu_active"=>"billing","breadcrumb"=>true,"user"=>$user]);
    }

    public function savePauseAccount(Request $request){
        $user = User::getCurrent();
        $activation_day=$request->get("activation_day");

        if($user->pause_account){
            $user->pause_account->delete();
        }

        $pause_account = new PauseAccount();
        $pause_account->user_id = $user->id;
        $pause_account->activation_day = $activation_day;
        $pause_account->save();

        $user_subscription=$user->getCurrentSubscription();

        if(!$user_subscription){
            return redirect()->route("billing")->with(["message_info"=>"You don't have an active subscription"]);
        }

        try {
            $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
            Log::info('Canceling Subscription For: '.$user->email.' '.var_export(isset($result->message)?$result->message:$result,true));
            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
        } catch (\Exception $e){
            Log::error('Error Cancel Subscription: '.var_export($e->getMessage(),true));
        }

        if($user_subscription->plan=="baselang_hourly"){
            Subscription::where("id",$user_subscription->id)->delete();
        } elseif($user_subscription->status=="future"){
            $start_date=\DateTime::createFromFormat("Y-m-d",$user_subscription->starts_at)->sub(new \DateInterval('P30D'));
            Subscription::where("id",$user_subscription->id)->update(["starts_at"=>$start_date->format("Y-m-d"),"ends_at"=>$user_subscription->starts_at,"status"=>"cancelled"]);
        } else {
            Subscription::where("id",$user_subscription->id)->update(["status"=>"cancelled"]);
        }

        $user->subscription=$user->getCurrentSubscription();
        $user->activation_day=$pause_account->activation_day;

        $date1 = $user->subscription->status=="future"?\DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at):\DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at);
        $date2 = new \DateTime($user->activation_day);
        $diff = $date1->diff($date2);
        $days=$diff->days;

        if($days==13){
            $time="2 weeks";
        }elseif($days==27){
            $time="1 month";
        }elseif($days==41){
            $time="6 weeks";
        }elseif($days==57){
            $time="2 months";
        }elseif($days==89){
            $time="3 months";
        }

        return view("user.success_pause_account",["menu_active"=>"billing","breadcrumb"=>true,"user"=>$user,"time"=>$time]);
    }

    public function getBilling(){

        Cache::flush();
        session()->forget("current_subscription");
        $user = User::getCurrent();
        if(!$user){
            return redirect()->route("login")->with(["message_info"=>"Your session has expired"]);
        }

        $subscriptionType=session("current_subscription");
        if($subscriptionType=="inmersion") {
            return redirect()->route("inmersion_billing");
        }

        $user->refreshSubscriptionSession();
        $user->updateSubscriptionInfo();
        $user->plan=$this->getPlan();
        $user->last_plan=false;
        $user->subscription=$user->getCurrentSubscription();
        $user->last_subscription=false;

        if((!$user->subscription || !$user->plan) && $user->last_unlimited_subscription){
            $user->last_plan=$this->getPlan($user->last_unlimited_subscription);
        }

        if($user->subscription && $user->subscription->status=="future"){
            $user->last_subscription=Subscription::where("id",$user->subscription->id)->first();
            $user->last_plan=$this->getPlan($user->last_subscription->plan);
        }

        $days=false;

        if($user->pause_account){
            $date1 = new \DateTime(gmdate("Y-m-d"));
            $date2 = new \DateTime($user->pause_account->activation_day);
            $diff = $date1->diff($date2);
            $days=$diff->days;
        }

        $plans=Subscription::getPlans();
        $prorated_amount_one = false;

        if($user->subscription && $user->dele_trial_test && @!$user->dele_trial_test->from && @!$user->active_dele_trial){
            if($user->subscription->plan=="baselang_dele_trial"){

                //To DELE
                $difference_amount = $plans["baselang_dele"][1]-$plans["baselang_149"][1];
                $daily_amount = $difference_amount/31;

                $date_one = new \DateTime($user->subscription->ends_at);
                $date_two = new \DateTime($user->dele_trial_test->ends_at_last_subscription);
                $diff = $date_one->diff($date_two);
                $diff_days = $diff->days;

                if($diff_days==0){
                    $diff_days=1;
                }
                $prorated_amount_one = $difference_amount-($daily_amount*$diff_days);
                $prorated_amount_one = round($prorated_amount_one,2);
            }
        }

        $prorated_gold = false;
        if($user->buy_prebooks()->first() && $user->buy_prebooks()->first()->type=="silver"){
            //To Gold
            $difference_amount = 200;
            $daily_amount = $difference_amount/365;

            $date_one = new \DateTime(gmdate("Y-m-d"));;
            $date_two = \DateTime::createFromFormat("Y-m-d",$user->buy_prebooks()->first()->activation_date)->add(new \DateInterval("P1Y"));
            $diff = $date_one->diff($date_two);
            $diff_days = $diff->days;

            if($diff_days==0){
                $diff_days=1;
            }
            $prorated_gold = $daily_amount*$diff_days;
            $prorated_gold = round($prorated_gold,2);
        }

        $msg_dele_trial = false;
        if($user->subscription && $user->dele_trial_test && $user->dele_trial_test->completed==0){
            $msg_dele_trial = "Your trial will end on";
        }

        $user->refreshSubscriptionSession();

        if($user->subscription && in_array($user->subscription->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
            $location = Location::where("name", "medellin")->first();
            if($location && $location->id!=$user->location_id){
                \Log::info("Location assignment: ".$location->id." - ".$location->name);
                $user->location_id=$location->id;
                User::where("id",$user->id)->update(["location_id"=>$location->id, "last_unlimited_subscription"=>$user->subscription->plan]);
            }
        }
        //Next locations...

        if($user->last_plan && !$user->last_plan->family && $user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule) {
            $user->last_plan->family="real";
        }

        return view("user.billing",["menu_active"=>"billing","user"=>$user,"days"=>$days,"prorated_amount_one"=>$prorated_amount_one,"msg_dele_trial"=>$msg_dele_trial,"prorated_gold"=>$prorated_gold]);
    }

    public function getInmersionBilling(){
        $user = User::getCurrent();

        $subscriptionType=session("current_subscription");
        if($subscriptionType!="inmersion") {
            return redirect()->route("billing");
        }

        $inmersion = $user->inmersions_without_paying->sortBy("inmersion_start")->first();

        return view("inmersion.billing", ["menu_active"=>"billing", "inmersion"=>$inmersion]);
    }

    public function getFreeTime(){
        $enable_hidden=false;
        $user = User::getCurrent();

        if($user->location_id) {
            return redirect()->route("dashboard");
        }

        $cancelation=Statistics::where("user_id",$user->id)->where("data_x","cancel_free_time")->first();
        if($cancelation){
            $enable_hidden=true;
        }

        return view("user.free_time",["enable_hidden"=>$enable_hidden,"menu_active"=>"refer"]);
    }

    public function changeRol($rol){
        $user = User::getCurrent();

        if($user->hasRole($rol)){
            $rol=Role::where("name",$rol)->first();
            session(['current_rol' => $rol->id]);
        }

        return redirect()->route("dashboard")->with(["message_info"=>"Your rol has change"]);
    }

    public function saveZoom(Request $request){
        $user = User::getCurrent();
        $data=$request->only(["zoom_email"]);

        $user->zoom_email=$data["zoom_email"];
        User::where("id",$user->id)->update(["zoom_email"=>$data["zoom_email"]]);

        Log::info("Zoom Email Updated:".$user->zoom_email);

        return redirect()->route("classes_new")->with(["message_info"=>"Your Zoom account was saved"]);;

    }

    public function getZoomFill(){
        $user = User::getCurrent();

        if($user->zoom_email){
            return redirect()->route("classes_new");
        }

        return view("user.zoom_fill",["menu_active"=>"classes","breadcrumb"=>true]);
    }

    public function getAdminDashboard(){
        return view("main.admin_dashboard",["menu_active"=>"dashboard"]);
    }

    public function getTeacherDashboard(){
        $user = User::getCurrent();

        $classes=Classes::where("class_time",">=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("teacher_id",$user->id)->get();

        return view("main.teacher_dashboard",["menu_active"=>"dashboard","classes"=>$classes]);
    }




    public function getDashBoard(){
        $user = User::getCurrent();
		if($user->isInmersionStudent() && $user->location_id != 2)
		{
			return redirect()->route("city_information");
		}
        
        $subscriptionType=session("current_subscription");

        $rol=$user->getCurrentRol();
        if($rol){

            if($rol->name=="teacher"){
                return redirect()->route("teacher_classes");
            }

            if($rol->name=="admin"){
                return redirect()->route("admin_dashboard");
            }
        }

        $classes=Classes::where("class_time",">=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("type",$subscriptionType)->where("user_id",$user->id)->get();

        foreach($classes as $class) {
            if(isset($class->location_id)) {
                $location = Location::find($class->location_id);
                if($location) {
                    $class->location = ucwords(strtolower($location->name));
                }else {
                    $class->location = "undefined";
                }
            }else {
                $class->location = "online";
            }
        }

        if($user->favorite_teacher){
            $teachers = Role::where('name','teacher')->first()->users()->where("id","<>",$user->favorite_teacher)->where("activated",1)->where("block_online",0)->orderBy("first_name","asc")->get();
        } else{
            $teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->where("block_online",0)->orderBy("first_name","asc")->get();
        }

        if($subscriptionType=="real"){
            $teachers=$teachers->where("is_deleteacher",0);

        } else {
            $teachers=$teachers->where("is_deleteacher",1);
        }

        $array_one=collect();
        $array_two=collect();
        $array_final=collect();
        foreach($teachers as $teacher){
            if($teacher->getEvaluatedCurrent()){
                $teacher->evaluation_student=$teacher->getEvaluatedCurrent()->evaluation;
                $array_one->push($teacher);
            } else{
                $teacher->evaluation_student=0;
                $array_two->push($teacher);
            }
        }
        $array_one=$array_one->sortByDesc('evaluation_student');

        if($user->favorite_teacher){
            $teacher=User::where("id",$user->favorite_teacher)->first();
            $array_final->push($teacher);
        }

        foreach($array_one as $item){
            $array_final->push($item);
        }

        foreach($array_two as $item){
            $array_final->push($item);
        }

        $first_teacher=$array_final->first()->id;

        //First teacher of Location
        $first_teacher_location=null;
        if($user->location_id) {
            if($user->favorite_teacher){
                $teachers = Role::where('name','teacher')->first()->users()->where("id","<>",$user->favorite_teacher)->where("activated",1)->where("location_id",$user->location_id)->orderBy("first_name","asc")->get();
            } else{
                $teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->where("location_id",$user->location_id)->orderBy("first_name","asc")->get();
            }

            if($subscriptionType=="real"){
                $teachers=$teachers->where("is_deleteacher",0);

            } else {
                $teachers=$teachers->where("is_deleteacher",1);
            }

            if(count($teachers)>0) {
                $array_one=collect();
                $array_two=collect();
                $array_final=collect();
                foreach($teachers as $teacher){
                    if($teacher->getEvaluatedCurrent()){
                        $teacher->evaluation_student=$teacher->getEvaluatedCurrent()->evaluation;
                        $array_one->push($teacher);
                    } else{
                        $teacher->evaluation_student=0;
                        $array_two->push($teacher);
                    }
                }
                $array_one=$array_one->sortByDesc('evaluation_student');

                if($user->favorite_teacher){
                    $teacher=User::where("id",$user->favorite_teacher)->first();
                    $array_final->push($teacher);
                }

                foreach($array_one as $item){
                    $array_final->push($item);
                }

                foreach($array_two as $item){
                    $array_final->push($item);
                }

                $first_teacher_location=$array_final->first()->id;
            }

        }

        return view("main.dashboard",["level_progress"=>$user->getProgress(),"menu_active"=>"dashboard","first_teacher"=>$first_teacher,"first_teacher_location"=>$first_teacher_location,"classes"=>$classes]);
    }

    public function restartSubscriptionNow($token){

        $paused_account=PauseAccount::where("token",$token)->first();

        if(!$paused_account){
            return redirect()->route("billing")->withErrors(['The token to verify is not assigned to a user.']);
        }

        $user = $paused_account->user;

        if(!$user){
            return redirect()->route("billing")->withErrors(['User was not found.']);
        }

        $current_user = User::getCurrent();

        if(!$current_user || ($current_user && $user->id!=$current_user->id)){
            Auth::loginUsingId($user->id, true);
        }

        $currentSubscription=$user->getCurrentSubscription();
        Subscription::where("user_id",$user->id)->delete();
        try {
            if($currentSubscription && $currentSubscription->status=="cancelled") {
                //CANCELED
                if($currentSubscription->ends_at>gmdate("Y-m-d")){
                    $start_date=\DateTime::createFromFormat("Y-m-d",$currentSubscription->ends_at)->format("Y-m-d");
                    $result = \ChargeBee_Subscription::create([
                        'paymentMethodToken' => $user->payment_method_token,
                        'planId' => $user->last_unlimited_subscription,
                        'firstBillingDate' => $start_date,
                    ]);
                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    $user->pause_account->delete();
                    $user->updateSubscriptionInfo();

                    return redirect()->route("billing")->with(["message_info"=>"Your subscription is active now"]);
                }
            } elseif($currentSubscription && ($currentSubscription->status=="future" || $currentSubscription->status=="active")){
                //USER HAVE ANOTHER SUBSCRIPTION
                $user->pause_account->delete();
                $user->updateSubscriptionInfo();
                return redirect()->route("billing")->withErrors(['You already have an active subscription.']);
            } else {
                //NO SUBSCRIPTION
                $result = \ChargeBee_Subscription::create([
                    'paymentMethodToken' => $user->payment_method_token,
                    'planId' => $user->last_unlimited_subscription
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            }



            $user->pause_account->delete();
            $user->updateSubscriptionInfo();
            return redirect()->route("billing")->with(["message_info"=>"Your subscription is active now"]);
        } catch (\Exception $e){
            \Log::error("Error RestartSubscriptionNow - User: ".$user->email." ERROR: ".var_export($e->getMessage()));
            return redirect()->route("billing")->withErrors(['Payment Method not Validated.']);
        }
    }

    public function restartSubscriptionAfter($token, $month=false){

        $paused_account=PauseAccount::where("token",$token)->first();

        if(!$paused_account){
            return redirect()->route("billing")->withErrors(['The token to verify is not assigned to a user.']);
        }

        $user = $paused_account->user;

        if(!$user){
            return redirect()->route("billing")->withErrors(['User was not found.']);
        }

        $current_user = User::getCurrent();

        if(!$current_user || ($current_user && $user->id!=$current_user->id)){
            Auth::loginUsingId($user->id, true);
        }

        $currentSubscription=$user->getCurrentSubscription();
        if(!$currentSubscription || ($currentSubscription && $currentSubscription->status=="cancelled")){

            $date=\DateTime::createFromFormat("Y-m-d",$user->pause_account->activation_day)->add(new \DateInterval("P14D"));

            if($month){
                $date->add(new \DateInterval("P14D"));
            }

            $user->pause_account->update(["activation_day"=>$date]);
            $user->pause_account->update(["token"=>null]);
            return view("user.info_restarting",["date"=>$date,"month"=>$month]);
        }

        //USER HAVE ANOTHER SUBSCRIPTION: Active or Pending
        return redirect()->route("billing")->withErrors(['You already have an active subscription.']);
    }

    public function stopPauseSubscription()
    {
        $user = User::getCurrent();

        if($user && $user->pause_account){
            $user->pause_account->delete();
            return redirect()->route("billing")->with(["message_info"=>"Successful cancellation."]);
        }

        return redirect()->route("billing")->withErrors(['It does not currently have a pause or cancellation of the subscription.']);
    }

    public function stopPauseSubscriptionToken($token)
    {
        $paused_account=PauseAccount::where("token",$token)->first();

        if(!$paused_account){
            return redirect()->route("billing")->withErrors(['The token to verify is not assigned to a user.']);
        }

        $user = $paused_account->user;

        if(!$user){
            return redirect()->route("billing")->withErrors(['User was not found.']);
        }

        $current_user = User::getCurrent();

        if(!$current_user || ($current_user && $user->id!=$current_user->id)){
            Auth::loginUsingId($user->id, true);
        }

        return view("user.fully_cancel",["menu_active"=>"billing","breadcrumb"=>true,"user"=>$user]);
    }

    public function getFullyCancel()
    {
        $user = User::getCurrent();

        if(!$user->pause_account){
            return redirect()->route("billing")->withErrors(['You do not have an active pause.']);
        }

        return view("user.fully_cancel",["menu_active"=>"billing","breadcrumb"=>true,"user"=>$user]);
    }

    public function getFeedback()
    {
        return view("user.feedback",["menu_active"=>"feedback"]);
    }

    public function saveFeedback(Request $request)
    {
        $user = User::getCurrent();
        $feedback_sent=$request->get("feedback");

        if(!$feedback_sent){
            return redirect()->back()->withErrors(["You can not send an empty feedback."]);
        }

        $feedback = new Feedback();
        $feedback->user_id = $user->id;
        $feedback->feedback = $feedback_sent;
        $feedback->save();

        try {
            if(\App::environment('production')){
                \Mail::send('emails.user_feedback', ["user"=>$user,"feedback"=>$feedback], function($message) use($user)
                {
                    $message->subject("New feedback of ".$user->email);
                    $message->to("info@buscatupaz.com","Buscatupaz.com");
                    $message->bcc("carlosdevia@imaginacolombia.com","Carlos Devia");
                });
            }
        } catch (\Exception $e) {
            Log::error('Cant send email: '.$e->getMessage());
        }



        return redirect()->back()->with(["message_info"=>"Feedback sent successfully!"]);
    }

    public function getPrebook()
    {
        $user=User::getCurrent();

        if($user->location_id || count($user->buy_prebooks)==0) {
            return redirect()->route("billing");
        }

        if($user->buy_prebooks()->first()) {
            return redirect()->route("billing");
        }

        $plans=Subscription::getPlans();

        $plan=null;
        $currentSubscription=$user->getCurrentSubscription();

        if($currentSubscription) {
            $plan=$plans[$currentSubscription->plan][0];
        }

        return view("user.prebook",["plan"=>$plan]);
    }

    public function buyPrebook(Request $request)
    {
        $user=User::getCurrent();
        $type=$request->get("type");

        if($type=="gold") {
            $amount='299.00';
            $hours=15;
        }else{
            $type="silver";
            $amount='99.00';
            $hours=5;
        }

        $active_preebook = $user->buy_prebooks()->first();
        if($active_preebook){
            \Log::info("Prebook already bought: ".$user->email." Active: ".$active_preebook->id." y ".$active_preebook->type." Sent: ".$type);
            return redirect()->route("billing")->with(["message_info"=>"You have successfully purchased the ".$active_preebook->type." prebook!"]);
        }

        try {
            \Log::info("BUY Prebook for: ".$user->email." Type: ".$type." Amount: ".$amount." Hours: ".$hours." PMT: ".$user->payment_method_token);
            $result = \ChargeBee_Transaction::createAuthorization([
                'amount' => $amount,
                'paymentMethodToken' => $user->payment_method_token,
                'descriptor' => [
                    'name' => 'BASELANG    *Prebook'
                ],
                'options' => [
                    'submitForSettlement' => True,
                    'paypal' => [
                        'description'=> 'BaseLang Prebook'
                    ]
                ]
            ]);

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

            if($result->success){
                $buy_prebook = new BuyPrebook();
                $buy_prebook->user_id = $user->id;
                $buy_prebook->type = $type;
                $buy_prebook->hours = $hours;
                $buy_prebook->status = 1;
                $buy_prebook->activation_date = gmdate("Y-m-d");
                $buy_prebook->save();

                \Log::info("Register Prebook for: ".$user->email." Register: ".$buy_prebook->id." Type: ".$buy_prebook->type);
                return redirect()->route("billing")->with(["message_info"=>"You have successfully purchased the ".$type." prebook!"]);
            }
        } catch(\Exception $e) {
            if(isset($result)){
                \Log::error('Error Buy Prebook: '.var_export($result->message,true));
            } else {
                \Log::error('Error Buy Prebook: '.var_export($e->getMessage(),true));
            }
            return redirect()->route("billing")->withErrors(['Error Payment Method']);
        }
    }

    public function upgradePrebookGold()
    {
        $user=User::getCurrent();
        $current_prebook = $user->buy_prebooks()->first();

        $amount = false;
        if($current_prebook && $current_prebook->type=="silver"){
            //To Gold
            $difference_amount = 200;
            $daily_amount = $difference_amount/365;

            $date_one = new \DateTime(gmdate("Y-m-d"));;
            $date_two = \DateTime::createFromFormat("Y-m-d",$user->buy_prebooks()->first()->activation_date)->add(new \DateInterval("P1Y"));
            $diff = $date_one->diff($date_two);
            $diff_days = $diff->days;

            if($diff_days==0){
                $diff_days=1;
            }
            $amount = $daily_amount*$diff_days;
            $amount = round($amount,2);
        } else {
            \Log::error("Error on upgrade to Gold, no current prebook or silver: for: ".$user->email." Current: ".var_export($current_prebook,true));
            return redirect()->route("billing")->withErrors(['An error occurred while processing your request. Please try Again.']);
        }

        if(!$amount){
            \Log::error("Error on upgrade to Gold with amount: ".$amount." for: ".$user->email);
            return redirect()->route("billing")->withErrors(['An error occurred while processing your request. Please try Again.']);
        }

        try {
            \Log::info("Prebook Upgrade: for: ".$user->email." amount: ". $amount." OLD: ".$current_prebook->type." id: ".$current_prebook->id);
            $result = \ChargeBee_Transaction::createAuthorization([
                'amount' => $amount,
                'paymentMethodToken' => $user->payment_method_token,
                'options' => [
                    'submitForSettlement' => true,
                ]
            ]);

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

            $buy_prebook = $user->buy_prebooks()->first();
            BuyPrebook::where("id",$buy_prebook->id)->update(["type"=>"gold","hours"=>15]);
            \Log::info("Prebook Upgrade: ".$buy_prebook->id);

        } catch(\Exception $e) {
            \Log::error('Error Upgrade Gold PHP: '.var_export($e->getMessage(),true));
            if(isset($result)) {
                \Log::error('Error Upgrade Gold: ' . var_export($result, true));
            }

            return redirect()->route("billing")->withErrors(['An error occurred with your Payment Method. Please try again or change your payment method']);
        }

        return redirect()->route("billing")->with(["message_info"=>"You have successfully updated your prebook to gold!"]);
    }

    public function saveReadPrebook()
    {
        $user = User::getCurrent();

        if(!$user->location_id || $user->read_prebook) {
            return redirect()->route("prebook");
        }

        User::where("id",$user->id)->update(["read_prebook"=>1]);

        return redirect()->route("prebook");
    }

    public function saveActiveLocation(Request $request)
    {
        $user = User::getCurrent();
        $firstBillingDate = $request->get("firstBillingDate");
        $subscription = $request->get("subscription");

        if(isset($firstBillingDate) && $firstBillingDate <= gmdate("Y-m-d")){
            return redirect()->route("change_location")->withErrors(["The date must be greater than the current day!"]);
        }

        $date_to_schedule = \DateTime::createFromFormat('Y-m-d', $firstBillingDate)->sub(new \DateInterval('P5D'))->format("Y-m-d");
        $trial_payday = \DateTime::createFromFormat('Y-m-d', $firstBillingDate)->sub(new \DateInterval('P1D'))->format("Y-m-d");
        $plans = Subscription::getPlans();
        $price = $plans[$subscription][1];

        if(!$user->active_locations){

            $active_location = new ActiveLocation();
            $active_location->user_id = $user->id;
            $active_location->activation_day = $firstBillingDate;
            $active_location->date_to_schedule = $date_to_schedule;
            $active_location->trial_payday = $trial_payday;
            $active_location->plan = $subscription;
            $active_location->price = $price;
            $active_location->save();
            Log::info("New ActiveLocation: ".$active_location);

        }else{

            $user->active_locations->update(["activation_day"=>$firstBillingDate, "date_to_schedule"=>$date_to_schedule, "trial_payday"=>$trial_payday, "plan"=>$subscription, "price"=>$price]);
            Log::info("Update ActiveLocation: ".$user->active_locations);

        }

        return redirect()->route("billing")->with(["message_info"=>"Operation carried out successfully!"]);

    }

    public function startLocationActivation(Request $request)
    {
        $user=User::getCurrent();

        if($user->active_locations){

            $user_subscription=$user->getCurrentSubscription();

            if($user_subscription){

                try {

                    $changes=['price'=>$user->active_locations->price, 'planId'=>$user->active_locations->plan, 'options'=>['prorateCharges'=>true]];
                    $result=\Chargebee_Subscription::update($user_subscription->subscription_id, $changes);
                    $user->last_unlimited_subscription=$user->active_locations->plan;
                    User::where("id",$user->id)->update(["last_unlimited_subscription"=>$user->active_locations->plan]);

                    if($result->success) {

                        Log::info("Change of plan online (".$user_subscription->plan.") to plan in-person (".$user->active_locations->plan.")");
                        //BuyPrebook::where("user_id",$user->id)->where("type","silver")->where("status",1)->delete();

                        try {
                            if(\App::environment('production')) {
                                //Email to Niall and Thomas
                                $subscription=$user->active_locations->plan;
                                \Mail::send('emails.new_user_location', ["user" => $user, "subscription" => $subscription], function ($message) use ($user, $subscription) {
                                    $message->subject("New student - ".$subscription);
                                    $message->bcc(['info@buscatupaz.com' => 'Niall', 'carlosdevia@imaginacolombia.com' => 'Carlos Devia']);
                                });
                            }
                        } catch (\Exception $e) {
                            Log::error('Cant send email: '.$e->getMessage());
                        }

                        $user->active_locations->delete();
                        $user->updateSubscriptionInfo();

                    }

                } catch (\Exception $e) {
                    Log::error('Error Updating Subscription ID: '.var_export($e->getMessage(),true));
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

            }else{

                try {
                    $result = \ChargeBee_Subscription::create([
                        'paymentMethodToken' => $user->payment_method_token,
                        'planId' => $user->active_locations->plan
                    ]);

                    $subscription = $user->active_locations->plan;

                    if($result->success) {

                        Log::info("New plan in-person (".$subscription.")");

                        try {
                            if(\App::environment('production')) {
                                //Email to Niall and Thomas
                                \Mail::send('emails.new_user_location', ["user" => $user, "subscription" => $subscription], function ($message) use ($user, $subscription) {
                                    $message->subject("New student - ".$subscription);
                                    $message->bcc(['info@buscatupaz.com' => 'Niall', 'carlosdevia@imaginacolombia.com' => 'Carlos Devia']);
                                });
                            }
                        } catch (\Exception $e) {
                            Log::error('Cant send email: '.$e->getMessage());
                        }

                        $user->active_locations->delete();
                        $user->updateSubscriptionInfo();

                    }

                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                    Log::info("Subscription Done: ".$user->email." Subscription: ".$subscription." Result: ".var_export($result->subscription->id,true));

                    Subscription::where("user_id",$user->id)->delete();
                } catch (\Exception $e){
                    Log::error("Subscription Error: ".$user->email." Subscription: ".$user->active_locations->plan." Result: ".var_export($e->getMessage(),true));
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

            }

        }else{
            return redirect()->route("billing")->with(["message_info"=>"Currently you do not have a pending plan to activate!"]);
        }

        return redirect()->route("billing")->with(["message_info"=>"Your plan change has been activated!"]);
    }

    public function changeLocationDate(Request $request)
    {
        $user = User::getCurrent();
        $firstBillingDate = $request->get("firstBillingDate");

        if(isset($firstBillingDate) && $firstBillingDate <= gmdate("Y-m-d")){
            return redirect()->route("billing")->withErrors(["The date must be greater than the current day!"]);
        }

        if($user->active_locations){

            $date_to_schedule = \DateTime::createFromFormat('Y-m-d', $firstBillingDate)->sub(new \DateInterval('P5D'))->format("Y-m-d");
            $trial_payday = \DateTime::createFromFormat('Y-m-d', $firstBillingDate)->sub(new \DateInterval('P1D'))->format("Y-m-d");

            $user->active_locations->update(["activation_day"=>$firstBillingDate, "date_to_schedule"=>$date_to_schedule, "trial_payday"=>$trial_payday,]);

            $class_time = \DateTime::createFromFormat("Y-m-d H:i:s",$firstBillingDate." 23:59:59");
            $classes = Classes::where("class_time","<=",$class_time)->where("user_id",$user->id)->get();
            Log::info("Remove classes prior to this date: ".$firstBillingDate." - Number of classes: ".count($classes));
            Log::info("Classes: ".$classes);
            /*foreach($classes as $class){
                $class->removeZoom();
                $class->delete();
            }
            */

            Subscription::where("user_id",$user->id)->delete();

            Log::info("Update ActiveLocation: ".$user->active_locations);

        }else{
            return redirect()->route("billing")->with(["message_info"=>"Currently you do not have a pending plan to activate!"]);
        }

        return redirect()->route("billing")->with(["message_info"=>"Your plan change has been modified success!"]);
    }

    public function cancelActiveLocations(Request $request)
    {
        $user = User::getCurrent();

        if($user->active_locations){

            $class_time = \DateTime::createFromFormat("Y-m-d H:i:s",$user->active_locations->activation_day." 23:59:59");
            $classes = Classes::where("user_id",$user->id)->get();
            Log::info("Remove classes prior to this date: ".$user->active_locations->activation_day." - Number of classes: ".count($classes));
            Log::info("Classes: ".$classes);
            /*
            foreach($classes as $class){
                $class->removeZoom();
                $class->delete();
            }
            */

            Log::info("ActiveLocation to delete: ".$user->active_locations->id." - User: ".$user->id);
            $user->active_locations->delete();
            Subscription::where("user_id",$user->id)->delete();
        }else{
            return redirect()->route("billing")->with(["message_info"=>"Currently you do not have a pending plan to activate!"]);
        }

        return redirect()->route("billing")->with(["message_info"=>"Your plan change has been cancelled"]);

    }




    public function getProviderSignup() {

            $user = User::getCurrent();
            if(isset($user->id)) return redirect()->route("home");
            if(!$user) $user = new User();

            return view("user.create_provider",["breadcrumb"=>false,'user'=>$user]);

    }


    public function getUserSignup() {

        $user = User::getCurrent();
        if(isset($user->id)) return redirect()->route("home");
        if(!$user) $user = new User();

        return view("user.create_user",["breadcrumb"=>false,'user'=>$user]);

    }




}