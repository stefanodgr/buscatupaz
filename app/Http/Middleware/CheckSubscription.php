<?php

namespace App\Http\Middleware;

use App\Models\BuyPrebook;
use App\Models\Location;
use App\Models\Prebook;
use App\Models\Subscription;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = User::getCurrent();
        if(!$user){
            return redirect()->route("login");
        }

        if($user->isInmersionStudent() && !$user->isInmersionActive() && $user->subscriptionAdquired()){
            return redirect()->route("billing");
        }

        $rol=$user->getCurrentRol();

        if($rol->name=="admin"){
            return redirect()->route("admin_dashboard");
        }

        if($rol->name=="teacher"){
            return redirect()->route("teacher_classes");
        }

        if($rol->name=="coordinator" || $rol->name=="student"){
            $subscription=$user->subscriptions()->first();
            if(!$subscription){
                Subscription::create(["user_id"=>$user->id,"subscription_id"=>"BaseLang","status"=>"active","plan_name"=>"baselang_99","starts_at"=>gmdate("Y-m-d"),"ends_at"=>gmdate("Y-m-d")]);
            } elseif($subscription && $subscription->ends_at<gmdate("Y-m-d")){
                Subscription::where("id",$subscription->id)->update(["ends_at"=>gmdate("Y-m-d")]);
            }
        }

        if(!$user->is_subscribed && !$user->has_immersion && !$user->is_pending){
            if(!$user->payment_method_token){
                return redirect()->route('billing')->withErrors(["You don't have an active subscription.","You don't have a valid Payment Method"]);
            }
            return redirect()->route('billing');
        }
        
        $user->refreshSubscriptionSession();
        if($user->activated=="0"){
            Auth::logout();
            return redirect()->route("login");
        }

        $current_subscription = session('current_subscription');

        if(!isset($current_subscription)){

            $current_subscription=$user->getCurrentSubscriptionType();
            if($current_subscription=="dele_real"){
                $current_subscription="real";
            }

            session(['current_subscription' => $current_subscription=="real"?"real":"dele"]);
        }

        $subscription=$user->getCurrentSubscription();
        $user_subscription=$subscription;

        //Remove Location
        if($subscription && !in_array($subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"]) && !$user->isInmersionStudent() && $user->location_id){
            \Log::info("Remove location_id: ".$user->location_id);
            User::where("id",$user->id)->update(["location_id"=>null]);
        }

        //Agg Medellin

        try {
            if($subscription && in_array($subscription->plan->name,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                $location = Location::where("name", "medellin")->first();
                if($location && $location->id!=$user->location_id){
                    \Log::info("Location assignment: ".$location->id." - ".$location->name." - ".$location->timezone);
                    \Log::info("User: ".$user->id);
                    \Log::info("Plan: ".var_export($subscription,true));
                    \Log::info("Plan: ".$subscription->plan->name);
                    User::where("id",$user->id)->update(["location_id"=>$location->id, "last_unlimited_subscription"=>$subscription->plan->name?$subscription->plan->name:'', "timezone"=>$location->timezone]);
                }
            }
        } catch (\Exception $e){
            \Log::error('Check Plan Error: '.$e->getMessage().' Line: '.$e->getLine());
        }

        //Agg Location - Immersion
        try {
            if($user->isInmersionStudent() && $user->isInmersionFinalized()){
                $location = Location::where('id', $user->isInmersionStudent()->location_id)->first();
                if($location && $location->id!=$user->location_id){
                    \Log::info('Location assignment Immersion: '.$location->id." - ".$location->name." - ".$location->timezone);
                    \Log::info('User: '.$user->id);
                    User::where('id',$user->id)->update(['location_id'=>$location->id, 'timezone' =>$location->timezone]);
                }
            }
        } catch (\Exception $e){
            \Log::error('Check Immersion Error: '.$e->getMessage().' Line: '.$e->getLine());
        }

        $location = false;
        if($user->location_id){
            $location = Location::where("id", $user->location_id)->first();
            if(!$location){
                $location = false;
            }
        }

        View::share('location',$location);
        View::share('user', $user);

        return $next($request);
    }
}
