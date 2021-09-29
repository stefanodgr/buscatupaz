<?php

namespace App\Http\Controllers\User;


use App\Models\UserCancellation;
use App\Models\CancellationReason;
use App\Models\Error;
use App\Models\Location;
use App\Models\Plan;
use App\Models\Statistics;
use App\Models\Subscription;
use App\User;
use App\Models\Credits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use App\Models\UserCredits;
use Illuminate\Support\Facades\Log;
use App\Models\Classes;

class BillingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "billing");
    }

    public function getBilling(){

        $user = User::getCurrent();
        $user->refreshInformation();
        return view("user.billing",["user"=>$user]);
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

    public function getPaymentHistory($skip=null,$limit=3){
        $user = User::getCurrent();
        $payment_history=$user->getPaymentHistory($skip,$limit);

        return view("user.includes.billing_payment_history",["payments"=>$payment_history]);
    }

    public function resubscribe(){
        $user = User::getCurrent();
        $user->refreshInformation();
        if($user->is_subscribed){
            return redirect()->route("billing")->with(["message_info"=>"You already have an active subscription"]);
        }
        $plan = $user->subscription->plan->plan_id;
        if(in_array($plan, ["baselang_99","baselang_129"]))
        {
            $plan = 'baselang_149';
        }
        
        try {
            \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
                "planId" => $plan,
                "autoCollection" => "on",
                "trialEnd"=>0
            ]);

            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error creating subscription for customer',$e->getLine(),$e->getMessage());
            return redirect()->route("billing")->withErrors(["Your payment method was declined."]);
        }

        return redirect()->route("billing")->with(["message_info"=>"Your subscription has been updated."]);
    }


    public function getCancelSubscription(){
        $user = User::getCurrent();

        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }

        if($user->canPauseSubscription()){
            return redirect()->route('cancel_advice');
        }

        return redirect()->route('cancel_survey');
    }


    public function getCancelAdvice(){
        $user = User::getCurrent();
        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }
        if(!$user->canPauseSubscription()){
            return redirect()->route('billing');
        }
        return view("user.billing_cancel_advice",["menu_active"=>"billing", "breadcrumb"=>true,"hourly"=>Plan::getHourlyPlan(),"default_subscription"=>Subscription::getDefaultSubscription($user->getCurrentSubscription()->plan->type=='rw')]);
    }

    public function getCancelSurvey(){

        $user = User::getCurrent();

        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }

        $reasons=CancellationReason::where('status',1)->get();

        return view("user.billing_cancel_survey",["menu_active"=>"billing","breadcrumb"=>true,"reasons"=>$reasons]);
    }

    public function getCancelReason($reason){
        $user = User::getCurrent();

        $reason = CancellationReason::where('option',$reason)->where('status',1)->first();
        if(!$reason){
            return redirect()->route('cancel_survey')->withErrors(["The selected reason doesn't exist."]);
        }

        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }

        if (filter_var($reason->link, FILTER_VALIDATE_URL)) {
            return redirect()->to($reason->link);
        } elseif($reason->link){
            return redirect()->route($reason->link,[$reason->option]);
        }

        if(!$reason->title){
            return redirect()->route('cancel_confirm',['reason'=>$reason->option]);
        }

        return view("user.billing_cancel_reason",["menu_active"=>"billing","breadcrumb"=>true,"reason"=>$reason]);

    }

    public function enableExtraHourByCancel(){

        $user = User::getCurrent();

        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }
        $user->statistics()->create(['type'=>'double_credits','data_x'=>$user->subscription->plan->type,'data_y'=>$user->subscription->plan->name]);
        return redirect()->route('change_subscription_preview',['subscription'=>'baselang_hourly']);
    }

    public function enableFreeTimeByCancel(){

        $user = User::getCurrent();

        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }
        $user->statistics()->create(['type'=>'cancel_free_time','data_x'=>$user->subscription->plan->type,'data_y'=>$user->subscription->plan->name]);
        return redirect()->route('referral_page');
    }

     public function enabletakebreakCancel(){
        $user = User::getCurrent();
        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }
        $user->statistics()->create(['type'=>'cancel_take_break','data_x'=>$user->subscription->plan->type,'data_y'=>$user->subscription->plan->name]);
        return redirect()->route('referral_page');
    }

    public function getCancelConfirm($reason, Request $request){
        $user = User::getCurrent();
        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }

        $reason = CancellationReason::where('option',$reason)->where('status',1)->first();
        if(!$reason){
            return redirect()->route('cancel_survey')->withErrors(["The selected reason doesn't exist."]);
        }

        $default_plan = Plan::getDefaultPlan($user->subscription->plan->type=='rw');
        $hourly_plan = Plan::getHourlyPlan();

        return view("user.billing_cancel_confirm",["menu_active"=>"billing","reason"=>$reason,"feedback"=>$request->input('feedback'),"breadcrumb"=>true,"default_plan"=>$default_plan,"hourly_plan"=>$hourly_plan]);
    }


    public function getCancelPause($reason=null){
        $user = User::getCurrent();
        if(!$user->canPauseSubscription()){
            return redirect()->route('cancel_confirm',$reason);
        }

        return redirect()->route('pause_account');
    }


    public function cancelSubscription(Request $request){
        $data['reason_id'] = $request['reason'];
        $data['other'] = $request['other'];
        $user = User::getCurrent();
        $data['user_id']=$user->id;
        
        if(!$user->canCancelSubscription()){
            return redirect()->route('billing');
        }


        try {
            if($user->subscription->pause && $user->subscription->pause>gmdate('Y-m-d H:i:s')){
                \ChargeBee_Subscription::removeScheduledPause($user->subscription->subscription_id);
            } elseif($user->subscription->resume && $user->subscription->resume>gmdate('Y-m-d H:i:s')){
                \ChargeBee_Subscription::removeScheduledResumption($user->subscription->subscription_id);
            };
        } catch (\Exception $e){
            Error::reportError('Error canceling pause for subscription',$e->getLine(),$e->getMessage());
        }

        try {

            \ChargeBee_Subscription::cancel($user->subscription->subscription_id,[
                "endOfTerm" => $user->subscription->plan->type!='hourly' && $user->subscription->status!='in_trial'
            ]);
            $user->last_plan=$user->subscription->plan->plan_id;
            $user->secureSave();
            UserCancellation::create($data);
            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error canceling subscription',$e->getLine(),$e->getMessage());
            return redirect()->route('billing')->withErrors(["An error had occurred canceling your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been cancelled."]);

    }

    public function changeStartDate(Request $request){

        $user = User::getCurrent();
        try 
        {
			$plan = \ChargeBee_Plan::retrieve($user->subscription->plan->plan_id);
			$date=\DateTime::createFromFormat('Y-m-d H:i:s',$request->get('date').' 00:00:00');
			if($plan->plan()->trialPeriod){
				$date=\DateTime::createFromFormat('Y-m-d H:i:s',$request->get('date').' 00:00:00')->add(new \DateInterval("P".$plan->plan()->trialPeriod."D"));
			}

            \ChargeBee_Subscription::update($user->subscription->subscription_id,[
                "start_date" => $date->format('U')
            ]);
            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error on start now for future subscription',$e->getLine(),$e->getMessage());
            return redirect()->route('billing')->withErrors(["An error had occurred changing your subscription date."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription date has been changed."]);
    }

    public function startNow(){
        $user = User::getCurrent();
        try {
            \ChargeBee_Subscription::update($user->subscription->subscription_id,[
                "start_date" => 0
            ]);
        } catch (\Exception $e){
            Error::reportError('Error on start now for future subscription',$e->getLine(),$e->getMessage());
            return redirect()->route('billing')->withErrors(["An error had occurred trying to active your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription is active."]);

    }

    public function cancelUndo(){
        $user = User::getCurrent();

        try {
            \ChargeBee_Subscription::removeScheduledCancellation($user->subscription->subscription_id);
            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error canceling subscription',$e->getLine(),$e->getMessage());
            return redirect()->route('billing')->withErrors(["An error had occurred activating your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been re-activated."]);
    }


    public function getChangeLocation($preview=null){
        $user = User::getCurrent();
        $plans = false;

        if($user->subscription->plan->location->name == 'medellin')
        {
            $plans = $this->getOnlineGrandfatheredPlans();
        }
        else
        {
            $plans=Plan::where('location_name','<>',$user->subscription->plan->location->name)->orderBy('price','asc')->where('status',1)->get();
        }
 
        $locations = Location::where('name','<>',$user->subscription->plan->location->name)->get();
        $user->refreshInformation();
        
        return view("user.change_subscription",["menu_active"=>"billing","breadcrumb"=>true,"plans"=>$plans,"locations"=>$locations->pluck('name')->toArray(),"preview"=>$preview]);
    }

    public function getChangeSubscription($preview=null){

        $user = User::getCurrent();
        $user_subscription=$user->getCurrentSubscription();
        $plans = false;
        if($user_subscription->status=="active" &&  in_array($user_subscription->plan_name, ["baselang_129","baselang_129_trial"]))
        {
            $plans=Plan::where('location_name',$user->subscription->plan->location->name)->where('status',1)->whereNotIn('plan_id', ["baselang_149", "baselang_99"])->orderBy('price','asc')->get();
        }
        elseif($user_subscription->status=="active" && in_array($user_subscription->plan_name, ["baselang_99","baselang_99_trial"]))
        {
            $plans=Plan::where('location_name',$user->subscription->plan->location->name)->where('status',1)->whereNotIn('plan_id', ["baselang_149", "baselang_129"])->orderBy('price','asc')->get();
        }
        else
        {
            if($user_subscription->status=="active" && $user->subscription->plan->location->name == 'online' && !in_array($user_subscription->plan_name, ["baselang_149","baselang_149_trial"]))
            {
                $plans=$this->getOnlineGrandfatheredPlans();
            }
            else
            {
                $plans=Plan::where('location_name',$user->subscription->plan->location->name)->where('status',1)->whereNotIn('plan_id', ["baselang_129", "baselang_99"])->orderBy('price','asc')->get();
            }
        }

        if($user->last_plan){
            $plan = $plans->where('name',$user->last_unlimited_subscription->name)->first();
            if(!$plan){
               return redirect()->route('change_subscription')->withErrors(["An error had occurred changing your subscription."]);
            }
        }

        if($preview){
            if(!$plans->where('name',$preview)->first()){
                return redirect()->route('change_location_preview',['subscription'=>$preview]);
            };
        }
        $user->refreshInformation();
        return view("user.change_subscription",["menu_active"=>"billing","breadcrumb"=>true,"plans"=>$plans,"preview"=>$preview]);
    }

    public function getOnlineGrandfatheredPlans()
    {
        $location_name = 'online';
        $user = User::getCurrent();
        $plans = false;

        $subscriptions = \ChargeBee_Subscription::all([
            "customerId[is]" => $user->chargebee_id
        ]);
        $count_149 = $count_129 = $count_99 = 0;
        foreach($subscriptions as $subdata)
        {
            $sub = $subdata->subscription();
            if(in_array($sub->planId, ["baselang_149","baselang_149_trial"]))
            {
                $count_149++;
                break;
            }
            elseif(in_array($sub->planId, ["baselang_129","baselang_129_trial"]))
            {
                $count_129++;
            }
            elseif(in_array($sub->planId, ["baselang_99","baselang_99_trial"]))
            {
                $count_99++;
            }
        }
        if($count_149 > 0)
        {
            $plans=Plan::where('location_name', $location_name)->where('status',1)->whereNotIn('plan_id', ["baselang_129", "baselang_99"])->orderBy('price','asc')->get();
        }
        elseif($count_129 > 0 || $count_99 > 0)
        {
            $transactions = \ChargeBee_Transaction::all([
                "customerId[is]" => $user->chargebee_id
            ]);

            $convert_created_at = $user->created_at->format("Y-m-d");
            $first_date=\DateTime::createFromFormat("Y-m-d", $convert_created_at);
            $next_date=\DateTime::createFromFormat("Y-m-d", $convert_created_at)->add(new \DateInterval("P30D"));
            if($next_date->format("Y-m-d") > gmdate("Y-m-d")){
                $next_date = \DateTime::createFromFormat("Y-m-d", gmdate("Y-m-d"));
            }

            $i = $success = $stop = 0;
            while ($next_date->format("Y-m-d") <= gmdate("Y-m-d")) 
            {
                $j=0;
                $i++;
                
                foreach($transactions as $transdata)
                {
                    $trans = $transdata->transaction();
                    $trans_date = \DateTime::createFromFormat("U", $trans->date);
                    if($trans_date >= $first_date && $trans_date <= $next_date)
                    {
                        $success++;
                        unset($transactions[$j]);
                        break;
                    }
                    $j++;
                }
                if($stop == 1)
                {
                    break;
                }
                $first_date=$next_date->add(new \DateInterval("P1D"));
                if($first_date->format("Y-m-d") >= gmdate("Y-m-d"))
                {
                    break;
                }
                $next_date=$first_date->add(new \DateInterval("P1M"));
                if($next_date->format("Y-m-d") > gmdate("Y-m-d")){
                    $next_date = \DateTime::createFromFormat("Y-m-d", gmdate("Y-m-d"));
                    $stop = 1;
                }
            }

            if($i == $success){
                if($count_129 > 0){
                    $plans=Plan::where('location_name', $location_name)->where('status',1)->whereNotIn('plan_id', ["baselang_149", "baselang_99"])->orderBy('price','asc')->get();
                }
                elseif($count_99 > 0)
                {
                    $plans=Plan::where('location_name', $location_name)->where('status',1)->whereNotIn('plan_id', ["baselang_149", "baselang_129"])->orderBy('price','asc')->get();
                }
            }
            else
            {
                $plans=Plan::where('location_name', $location_name)->where('status',1)->whereNotIn('plan_id', ["baselang_129", "baselang_99"])->orderBy('price','asc')->get();
            } 
        }
        else
        {
            $plans=Plan::where('location_name', $location_name)->where('status',1)->whereNotIn('plan_id', ["baselang_129", "baselang_99"])->orderBy('price','asc')->get();
        } 
        return $plans;
    }

    public function changeSubscriptionNow($subscription){
        $user = User::getCurrent();
        $user_subscription=$user->getCurrentSubscription();

        try {
            if(!$user->canCancelSubscription()){

                \ChargeBee_Subscription::removeScheduledCancellation($user->subscription->subscription_id);
                $user->refreshInformation();
            }
        } catch (\Exception $e){
            Error::reportError('Error undo canceling subscription',$e->getLine(),$e->getMessage());
        }

        try {
            $plan = Plan::where('plan_id',$subscription)->first();

            if(!$plan){
                return redirect()->route('change_subscription')->withErrors(["An error had occurred changing your subscription."]);
            }

            if($user_subscription->plan_name == "baselang_hourly"){
                try
                {
                    Subscription::where("id",$user_subscription->id)->delete();
                    $booked_class=Classes::where("user_id",$user->id)->get();
                    foreach($booked_class as $class){
                        $class->delete();
                    }

                    $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                }
                catch (\Exception $e)
                {
                    Log::error('Error Deleting Subscription ID: '.var_export($e->getMessage(),true));
                }

                try
                {
                    $result = \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
                        "planId" => $plan->plan_id
                    ]);
                    $user->last_unlimited_subscription=$subscription;
                    User::where("id",$user->id)->update(["last_unlimited_subscription"=>$subscription]);
                    
                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                    Log::info("Subscription Done From baselang_hourly: ".$user->email." Subscription: ".$subscription." Result: ".var_export($result,true));
                }
                catch (\Exception $e)
                {
                    return redirect()->route("change_card")->withErrors(["Payment Method not Validated."]);
                }

            }elseif($user->subscription->subscription_id == "BaseLang"){
                \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
                    "planId" => $plan->plan_id
                ]); 
            }
            else{
                 \ChargeBee_Subscription::update($user->subscription->subscription_id,[
                    "planId" => $plan->plan_id,
                    "endOfTerm" => false,
                    "prorate" => $user->subscription->plan->price<$plan->price
                ]);
            }
            $user->last_plan=$user->subscription->plan->plan_id;
            $user->last_unlimited_subscription=$plan->name;
            $location = Location::where("name", $plan->location->name)->first();
            if($location && $location->id!=$user->location_id){
                $user->location_id=$location->id;
            }
            $user->secureSave();
            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error Changing Subscription Now',$e->getLine(),$e->getMessage());
            return redirect()->route('change_subscription')->withErrors(["An error had occurred changing your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been changed."]);
    }

    public function changeSubscriptionEnd($subscription){

        try {
            $user = User::getCurrent();
            $user->checkChange($subscription);
            if(!$user->canCancelSubscription()){
                \ChargeBee_Subscription::removeScheduledCancellation($user->subscription->subscription_id);
                $user->refreshInformation();
            }

        } catch (\Exception $e){
            Error::reportError('Error undo canceling subscription',$e->getLine(),$e->getMessage());
        }

        try {
            $plan = Plan::where('name',$subscription)->first();
            if(!$plan){
                return redirect()->route('change_subscription')->withErrors(["An error had occurred changing your subscription."]);
            }

            \ChargeBee_Subscription::update($user->subscription->subscription_id,[
                "planId" => $plan->plan_id,
                "endOfTerm" => true,
                "prorate" => false,
            ]);
            $user->last_plan=$user->subscription->plan->plan_id;
            $user->secureSave();
            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error Changing Subscription END',$e->getLine(),$e->getMessage());
            return redirect()->route('change_subscription')->withErrors(["An error had occurred changing your subscription."]);
        }
        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been changed."]);
    }


    public function changeCancel(){
        $user = User::getCurrent();

        if(!$user->subscription->change){
            return redirect()->route('change_subscription')->withErrors(["An error had occurred updating your subscription."]);
        }

        try {
            \ChargeBee_Subscription::removeScheduledChanges($user->subscription->subscription_id);
        } catch (\Exception $e){
            Error::reportError('Error Cancel Subscription Change',$e->getLine(),$e->getMessage());
            return redirect()->route('change_subscription')->withErrors(["An error had occurred updating your subscription."]);
        }
        $user->refreshInformation();

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been changed."]);
    }

    public function changeNow(){
        $user = User::getCurrent();

        if(!$user->subscription->change){
            return redirect()->route('change_subscription')->withErrors(["An error had occurred updating your subscription."]);
        }

        try {
            \ChargeBee_Subscription::update($user->subscription->subscription_id,[
                "planId" => $user->subscription->change,
                "endOfTerm" => false,
                "prorate" => $user->subscription->plan->price<$user->subscription->future->price
            ]);
            $user->last_plan=$user->subscription->plan->plan_id;
            $user->secureSave();
            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error Changing Subscription Now',$e->getLine(),$e->getMessage());
            return redirect()->route('billing')->withErrors(["An error had occurred updating your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been changed."]);
    }

    public function cancelNow(){
        $user = User::getCurrent();

        try {

            \ChargeBee_Subscription::cancel($user->subscription->subscription_id,[
                "endOfTerm" => false
            ]);

            $user->refreshInformation();
        } catch (\Exception $e){
            Error::reportError('Error canceling subscription',$e->getLine(),$e->getMessage());
            return redirect()->route('billing')->withErrors(["An error had occurred canceling your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been cancelled."]);
    }

    public function getPauseAccount($extend=false){
        $user = User::getCurrent();

        if(!$user->canPauseSubscription() && !$extend){
            return redirect()->route('billing')->withErrors(["You can't pause your subscription"]);
        }

        $pause_options = Subscription::getPauseOptions();

        return view("user.billing_pause_account",["menu_active"=>"billing","breadcrumb"=>true,"user"=>$user,"pause_options"=>$pause_options,"extend"=>$extend]);
    }

    public function pauseAccount(Request $request){
        $user = User::getCurrent();

        if(!$user->canPauseSubscription()){
            return redirect()->route('billing')->withErrors(["You can't pause your subscription"]);
        }

        $activation = $request->input('activation_day');
        $pause_options = Subscription::getPauseOptions();

        if(!isset($pause_options[$activation])){
            return redirect()->back()->withErrors(["You can't pause your subscription in the selected time"]);
        }

        try {
            \ChargeBee_Subscription::pause($user->subscription->subscription_id,[
                "pauseOption" => "end_of_term",
                "resumeDate" => $user->subscription->ends_at->add(new \DateInterval($activation))->format('U')
            ]);
        } catch (\Exception $e){
            Error::reportError('Error pausing account',$e->getLine(),$e->getMessage());
            return redirect()->back()->withErrors(["An error had occurred pausing your subscription."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription has been paused."]);

    }

    public function pauseUndo() {
        $user = User::getCurrent();

        try {
            \ChargeBee_Subscription::removeScheduledPause($user->subscription->subscription_id);
        } catch (\Exception $e){
            Error::reportError('Error Undo Pause',$e->getLine(),$e->getMessage());
            return redirect()->back()->withErrors(["An error had occurred undo your pause request."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription is active now."]);
    }

    public function pauseExtend(Request $request) {
        $user = User::getCurrent();

        if(!$user->subscription->status=='paused'){
            return redirect()->route('billing')->withErrors(["You can't extend your pause period."]);
        }

        try {
            $activation = $request->input('activation_day');
            $pause_options = Subscription::getPauseOptions();

            if(!isset($pause_options[$activation])){
                return redirect()->back()->withErrors(["You can't pause your subscription in the selected time"]);
            }

            \ChargeBee_Subscription::resume($user->subscription->subscription_id,[
                "resumeOption"=>"specific_date",
                "resumeDate"=> $user->subscription->resume->add(new \DateInterval($activation))->format('U')
            ]);
        } catch (\Exception $e){
            Error::reportError('Error Undo Pause',$e->getLine(),$e->getMessage());
            return redirect()->back()->withErrors(["An error had occurred undo your pause request."]);
        }

        return redirect()->route('billing')->with(["message_info"=>"Your subscription pause was extended."]);
    }

    public function getPauseExtend(){
        $user = User::getCurrent();

        if($user->subscription->status=='paused'){
            return $this->getPauseAccount(true);
        }

        return redirect()->route('billing')->withErrors(["You can't extend your pause period."]);
    }

    public function pauseResume(){
        $user = User::getCurrent();

        if(!$user->subscription->status=='paused'){
            return redirect()->route('billing')->withErrors(["You can't resume your subscription now."]);
        }

        try {
            \ChargeBee_Subscription::resume($user->subscription->subscription_id,array(
                "resumeOption" => "immediately",
                "chargesHandling" => "invoice_immediately",
                "unpaidInvoicesHandling" => "schedule_payment_collection"
            ));
        } catch (\Exception $e){
            return redirect()->route('billing')->withErrors(["An Error had occurred resuming your subscription now."]);
        }

        return redirect()->route('billing')->with(["message_info","You subscription is now active."]);
    }

    public function pauseCancel(){
        $user = User::getCurrent();

        if(!$user->subscription->status=='paused'){
            return redirect()->route('billing')->withErrors(["You can't resume your subscription now."]);
        }

        try {
            \ChargeBee_Subscription::removeScheduledResumption($user->subscription->subscription_id);
            $user->last_plan=$user->subscription->plan->plan_id;
            $user->secureSave();
        } catch (\Exception $e){
            return redirect()->route('billing')->withErrors(["An Error had occurred cancelling your subscription now."]);
        }

        return $this->cancelNow();
    }

    public function getCreditsBuy(){
        $user = User::getCurrent();

        if($user->subscription->plan->type!="hourly"){
            return redirect()->route("billing")->withErrors('You can not buy credits for your current subscription');
        }

        return view("user.credits",["menu_active"=>"credits"]);
    }

    public function buyCredits(Request $request){
        $user = User::getCurrent();

        if($user->subscription->plan->type!="hourly"){
            return redirect()->route("billing")->withErrors('You can not buy credits for your current subscription');
        }

        $credit_price=UserCredits::getCreditsPrice($request->get("valuetobuy"));
        $total=($credit_price*$request->get("valuetobuy")/2);
        $credits=$request->get("valuetobuy");

        try {
            Error::reportInfo('Buy credits for ' . $user->email);
            $result = \ChargeBee_Invoice::charge([
                "customerId" => $user->chargebee_id,
                //"amount" => $user->current_rol->name=="coordinator"?0:Credits::calculate(abs($request->valuetobuy))*100,
                "amount" => $total*100,
                "description" => "Baselang ".abs($request->valuetobuy)." Credits"
            ]);

            if($result->invoice()->status=='payment_due'){
                \ChargeBee_Invoice::voidInvoice($result->invoice()->id);
                throw new \Exception('Payment Fails');
            };

            $user->credits+=$request->valuetobuy;
            Error::reportinfo("Saving credits for ".$user->email." ".$user->id." : ".var_export($user->credits,true));
            $user->secureSave();
            Credits::create(["credits"=>$request->valuetobuy,"user_id"=>$user->id,"billing_cycle"=>0,"subscription_id"=>$result->invoice()->id]);

        } catch(\Exception $e) {
            Error::reportError('Error on buy credits',$e->getLine(),$e->getMessage());
            return redirect()->route("credits")->withErrors(['Your payment method rejected the charge. Please try again, contact your bank or <a href="'.route("billing").'">Click here to change your payment method</a>']);
        }

        return redirect()->route("credits")->with(["message_info"=>'Thanks for your purchase! We\'ve added '.$request->valuetobuy.'credit'.($request->valuetobuy==1?"":"s").' to your account.']);

    }
    

}