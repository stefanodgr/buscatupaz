<?php

namespace App\Http\Middleware;

use App\Models\BuyPrebook;
use App\Models\Location;
use App\Models\Prebook;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class CheckUser
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
            Log::error("No User Login, Login Out");
            return redirect()->route("login");
        }

        $subscription_check = session('subscription_check');
        $check_date = gmdate('Y-m-d H:i:s',strtotime("-1 days"));
        if(!$subscription_check || ($subscription_check && $subscription_check<$check_date)){
            $user->refreshInformation();
        }

        $user->getCurrentRol();
        $user->refreshSubscriptionSession();

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
