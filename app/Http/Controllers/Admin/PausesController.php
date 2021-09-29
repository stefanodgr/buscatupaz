<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\PauseAccount;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PausesController extends Controller
{
    public function getIndex(){
        return view("admin.pauses.list",["menu_active"=>"users","breadcrumb"=>true]);
    }

    public function getList(){
        $pauses = PauseAccount::all();

        $pause_list = [];

        foreach($pauses as $pause){
        	if($pause->user){
        		$start_date_pause = false;
        		if($pause->user->getcurrentsubscription()){
        			$start_date_pause = $pause->user->getcurrentsubscription()->ends_at;
        		} else{
        			$subscription = Subscription::where("user_id",$pause->user->id)->orderBy("ends_at","desc")->first();
        			if(!$subscription){
        			    continue;
                    }
        			$start_date_pause = $subscription->ends_at;
        		}
        		if($start_date_pause){
	            	$pause_list[] = [$pause->user->email." <span>".$pause->user->first_name." ".$pause->user->last_name."</span>",\DateTime::createFromFormat("Y-m-d",$start_date_pause)->format("Y/m/d"),\DateTime::createFromFormat("Y-m-d",$pause->activation_day)->format("Y/m/d"),'<a href="'.route("admin_pauses_edit",["pause_id"=>$pause->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a>'];        			
        		}
        	}
        }

        return response()->json(['data' => $pause_list]);
    }

    public function getEdit($pause_id){

        $edit_pause=PauseAccount::where("id",$pause_id)->first();

		$start_date_pause = false;
		if($edit_pause->user->getcurrentsubscription()){
			$start_date_pause = $edit_pause->user->getcurrentsubscription()->ends_at;
		} else{
			$subscription = Subscription::where("user_id",$edit_pause->user->id)->orderBy("ends_at","desc")->first();
			$start_date_pause = $subscription->ends_at;
		}

        return view("admin.pauses.edit",["menu_active"=>"users","breadcrumb"=>true,"edit_pause"=>$edit_pause,"start_date_pause"=>$start_date_pause]);
    }

    public function restartSubscriptionNow($pause_id){

        $paused_account=PauseAccount::where("id",$pause_id)->first();

        if(!$paused_account){
            return redirect()->route("admin_pauses")->withErrors(['The account to reactivate does not exist.']);
        }

        $user = $paused_account->user;

        if(!$user){
            return redirect()->route("admin_pauses")->withErrors(['User was not found.']);
        }

        $currentSubscription=$user->getCurrentSubscription();
        Subscription::where("user_id",$user->id)->delete();
        try {
            if($currentSubscription && $currentSubscription->status=="cancelled") {
                //CANCELED
                if($currentSubscription->ends_at>gmdate("Y-m-d")){
                    $start_date=\DateTime::createFromFormat("Y-m-d",$currentSubscription->ends_at)->format("Y-m-d");
                    $result = \Chargebee_Subscription::create([
                        'planId' => $user->last_unlimited_subscription,
                        'firstBillingDate' => $start_date,
                    ]);
                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                    $user->pause_account->delete();
                    $user->updateSubscriptionInfo();

                    return redirect()->route("admin_pauses")->with(["message_info"=>"The subscription of ".$user->email." is active now."]);
                }
            } elseif($currentSubscription && ($currentSubscription->status=="future" || $currentSubscription->status=="active")){
                //USER HAVE ANOTHER SUBSCRIPTION
                $user->pause_account->delete();
                $user->updateSubscriptionInfo();
                return redirect()->route("admin_pauses")->withErrors([$user->email." already has an active subscription."]);
            } else {
                //NO SUBSCRIPTION
                $result = \Chargebee_Subscription::create([
                    'planId' => $user->last_unlimited_subscription
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            }
            $user->pause_account->delete();
            $user->updateSubscriptionInfo();
            return redirect()->route("admin_pauses")->with(["message_info"=>"The subscription of ".$user->email." is active now."]);
        } catch (\Exception $e){
            \Log::error("Error RestartSubscriptionNow - User: ".$user->email." ERROR: ".var_export($e->getMessage()));
            return redirect()->route("admin_pauses")->withErrors(["The payment method of ".$user->email." is not validated."]);
        }
    }

    public function restartSubscriptionAfter(Request $request){

    	$pause_id=$request->get("pause_id");

        $paused_account=PauseAccount::where("id",$pause_id)->first();

        if(!$paused_account){
            return redirect()->route("admin_pauses")->withErrors(['The account to reactivate does not exist.']);
        }

        $user = $paused_account->user;

        if(!$user){
            return redirect()->route("admin_pauses")->withErrors(['User was not found.']);
        }

        $currentSubscription=$user->getCurrentSubscription();
        if(!$currentSubscription || ($currentSubscription && $currentSubscription->status=="cancelled")){

			$activation_day=$request->get("activation_day");
            $user->pause_account->update(["activation_day"=>$activation_day]);
            return redirect()->route("admin_pauses_edit",["pause_id"=>$paused_account->id])->with(["message_info"=>"Pause has been updated"]);
        }

        return redirect()->route("admin_pauses")->withErrors([$user->email." already has an active subscription."]);
    }

    public function pauseUndo($user_id){
        $user = User::where("id",$user_id)->first();

        if(!$user){
            return redirect()->route("admin_pauses")->withErrors(['User was not found.']);
        }

        $user_subscription=$user->getCurrentSubscription();

        if($user->pause_account){
            $user->pause_account->delete();
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
                $result = \Chargebee_Subscription::create([
                    'planId' => $user->last_unlimited_subscription,
                    'firstBillingDate' => $start_date,
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);


                Subscription::where("id",$user_subscription->id)->delete();
                $user->updateSubscriptionInfo();

                return redirect()->route("admin_pauses")->with(["message_info"=>"Subscription pause has been canceled."]);
            }
        } catch (\Exception $e){
            Log::error("Error on resubscribe: ".var_export($e->getMessage(),true)." Date: ".var_export($start_date,true));
            return redirect()->route("admin_pauses")->withErrors(["Error processing your request, try again."]);
        }

        return redirect()->route("admin_pauses");
    }
}
