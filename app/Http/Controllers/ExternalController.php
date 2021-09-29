<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\InmersionPayment;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ExternalController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function updateUser(Request $request) {
        try {
            Log::info('ExternalController Update: '. var_export($request->all(),true));
            $data = $request->all();

            $user = User::where("email",$data["email"])->first();
            if(!$user){
                throw new \Exception("User don't found.".$data["email"]);
            }

            User::where("email",$data["email"])->update(["describes"=>$data["describes"],"motivation"=>$data["motivation"],"how_find"=>$data["how_find"]]);

        } catch(\Exception $e) {
            Log::error('Error Logging Request Update: '.var_export($e->getMessage(),true));
            return response()->json(['status' => 'false']);
        }

        return response()->json(['status' => 'success']);
    }

    public function registerUser(Request $request) {
        try {
            Log::info('ExternalController Register: '. var_export($request->all(),true));
        } catch(\Exception $e) {
            Log::error('Error Logging Request Setup: '.var_export($e->getMessage(),true));
        }

        try {
            $data = $request->all();
            $referalemail=false;
            $referred=false;

            $user = new User();
            $user->password = Hash::make('12345');
            $user->activated = 1;
            $user->chargebee_id = '';
            $check_email = '';

            foreach($data["serialdata"] as $serialdata){
                if($serialdata["name"]=="fname"){
                    $user->first_name = $serialdata["value"];
                }

                if($serialdata["name"]=="lname"){
                    $user->last_name = $serialdata["value"];
                }

                if($serialdata["name"]=="email"){
                    $user->email = str_replace(' ','+',$serialdata["value"]);
                    $check_email = str_replace(' ','+',$serialdata["value"]);
                }

                if($serialdata["name"]=="location"){
                    $location = Location::where("name", $serialdata["value"])->first();
                    $user->location_id = $location->id;
                    if($location->id == 1){
						$user->timezone = $location->timezone;
					}
                }

                if($serialdata["name"]=="referral_email"){
                    $data['referral_email'] = str_replace(' ','+',$serialdata["value"]);
                }
                
                if($serialdata["name"]=="start_date"){
                    $date_to_verify = $serialdata["value"];

                    if($date_to_verify > gmdate("Y-m-d")) {
                        $user->check_landing_date = 1;
                    }
                }
            }

            $check_user = User::where("email",$check_email)->first();

            if($check_user) {

                $inmersion_payment = InmersionPayment::where("user_id",$check_user->id)->first();

                if($inmersion_payment && $check_user->chargebee_id) {

                    $result = \ChargeBee_Customer::update($user->chargebee_id, [
                            "cf_bl_referral_code" => '',
                    ]);

                    if($result->success) {
                        User::where("id",$check_user->id)->update(['referral_code'=>null]);
                        InmersionPayment::where("user_id",$check_user->id)->delete();
                        Log::info("Update Chargebee ID: ".$check_user->chargebee_id." Status:".var_export($result->success,true));
                        Log::info($result->customer);
                    }else {
                        Log::info("Error updating the Chargebee ID! - User: ".$check_user->email);
                    }

                }

            }else {
                $referral_email = isset($data["referral_email"])?str_replace(' ','+',$data["referral_email"]):false;
                if($referral_email && $user->email!=$referral_email){
                    $referalemail=true;

                    $referred=User::where("email",$referral_email)->first();
                    if($referred){
                        $user->referral_email = $referral_email;
                    } else {
                        $referalemail=false;
                    }
                }

                $user->save();
                $user->verifyRole();

                \Log::info("User created: ".$user->id." - ".$user->email);
                                
                $user->updateSubscriptionInfo(true);
                //SEND email register
                try {
                    if(\App::environment('production')){
                        \Mail::send('emails.user_welcome', ["user" => $user], function ($message) use ($user) {
                            $message->subject("Welcome to BaseLang! Here's your login info");
                            $message->to($user->email, $user->first_name);
                        });
                    }
                } catch (\Exception $e) {
                    Log::error('Cant send email: '.$e->getMessage());
                }

                if($referalemail && $referred && ($user->last_unlimited_subscription == 'baselang_149_trial' || $user->last_unlimited_subscription == 'baselang_149' || $user->last_unlimited_subscription == 'baselang_129_trial' || $user->last_unlimited_subscription == 'baselang_129')){
                    $sub = $user->getCurrentSubscription();
                    \Log::info("subscription id: ".$sub->subscription_id);
                    $result = \ChargeBee_Subscription::retrieve($sub->subscription_id);
                    $cb_sub = $result->subscription();
                    \Log::info($cb_sub->planId);
                    $ref_act_date = date('Y-m-d', $cb_sub->nextBillingAt);

                    $referred->freeDays()->create(["referred_id"=>$user->id,"available"=>1,"free_days"=>30, "ref_activation_date" => $ref_act_date]);
                    //SEND email refered

                    try {
                        if(\App::environment('production')) 
                        {
                            \Mail::send('emails.user_refered_signed', ["user"=>$user,"userReferal"=>$referred], function($message) use($referred)
                            {
                                $message->subject("Your friend signed up for BaseLang using your link!");
                                $message->to($referred->email,$referred->first_name);
                            });
                        }
                    } catch (\Exception $e) {
                        Log::error('Cant send email: '.$e->getMessage());
                    }
                }
            }

        } catch(\Exception $e) {
            Log::error('Error Logging Request: '.var_export($e->getMessage(),true));
            return response()->json(['status' => 'fail']);
        }

        return response()->json(['status' => 'success']);
    }

}