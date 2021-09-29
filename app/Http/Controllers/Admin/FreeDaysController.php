<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Subscription;
use App\Models\UserFreeDays;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class FreeDaysController extends Controller
{
    public function __construct() {
        View::share('menu_active','free_days');
    }

    public function getIndex() {
        return view("admin.free_days.create",["breadcrumb"=>true]);
    }

    public function create(Request $request) {
    	$current_user=User::getCurrent();
        $subscription=$request->get("subscription");
        $days=$request->get("days");

        Log::info("New Free Days from Admin - Subscription: ".$subscription." - Days: ".$days);
        $subscriptions=null;

        if($subscription=="Hourly") {
        	$subscriptions=Subscription::where("plan_name","baselang_hourly")->get();
        }elseif($subscription=="Real World") {
        	$subscriptions=Subscription::where("plan_name","baselang_99")->orWhere("plan_name","baselang_129")->orWhere("plan_name","baselang_149")->get();
        }elseif($subscription=="DELE") {
        	$subscriptions=Subscription::where("plan_name","baselang_dele")->get();
        }elseif($subscription=="Medellin Real World") {
        	$subscriptions=Subscription::where("plan_name","medellin_rw")->get();
        }elseif($subscription=="Medellin Real World 1199") {
                $subscriptions=Subscription::where("plan_name","medellin_rw_1199")->get();
        }elseif($subscription=="Medellin Real World Lite") {
                $subscriptions=Subscription::where("plan_name","medellin_rw_lite")->get();
        }elseif($subscription=="Medellin DELE") {
        	$subscriptions=Subscription::where("plan_name","medellin_dele")->get();
        }

        if($subscriptions && count($subscriptions)>0) {
        	Log::info("Number of subscriptions ".$subscription." to verify: ".count($subscriptions));
        	$count_subscriptions=0;
	    	foreach($subscriptions as $subs) {
				$user=$subs->user;
	    		if($user && ($subs->status=="active" || $subs->status=="future" || ($subs->status=="cancelled" && $subs->ends_at > gmdate("Y-m-d")))) {
			        $user->addFreeDays($days);
			        $user->updateSubscriptionInfo();
			        UserFreeDays::create(["user_id"=>$user->id, "referred_id"=>$current_user->id, "active"=>1, "claimed"=>1,"available"=>1, "free_days"=>$days, "admin"=>1]);
			        $count_subscriptions++;
	    		}
	    	}
	    	return redirect()->route("admin_free_days")->with(["message_info"=>$days." free days have been added to ".$count_subscriptions." subscriptions ".$subscription."!"]);
        }else {
        	return redirect()->route("admin_free_days")->withErrors(["There are no ".$subscription." subscriptions currently!"]);
        }

    }

    public function confirmFreeDays(Request $request) {
    	$user=User::getCurrent();
        $password=$request->get("password");

		if(\Hash::check($password, $user->password)) {
	     	return response()->json(['response'=>'success']);
		}else{
     		return response()->json(['response'=>'error']);
		}

    }
}
