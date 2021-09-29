<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Subscription extends Model
{
    public static $default_type = 'real';
    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'plan_name',
        'starts_at',
        'next_billing',
        'next_payment',
        'period_unit',
        'current_subscription',
        'change', 
        'pause',
        'resume',
        'ends_at',
        'status'
    ];

    protected $dates = [
        'starts_at','ends_at','created_at','updated_at','next_billing','pause','resume'
    ];
    
    public static function compareSubscriptions($main,$compare){

        if($main==$compare){
            return true;
        }

        $subscriptions = [];
        $subscriptions[] = ['baselang_129','baselang_99','baselang_149','baselang_129_trial','baselang_149_trial','baselang_99_trial','9zhg'];
        $subscriptions[] = ['baselang_dele','baselang_dele_trial','baselang_dele_test'];
        foreach($subscriptions as $subscription){
            if(in_array($main,$subscription) && in_array($compare,$subscription)){
                return true;
            }
        }

        return false;

    }

    /**
     * Before save the model, check fillable attributes to update model
     * @return Subscription
     */
    public function getResumeDays(){
        if($this->resume){
            return round(\DateTime::createFromFormat('U',$this->resume->format('U')-gmdate('U'))->format('U')/(60*60*24));
        }

        return 0;
    }

    public static function getPlans(){
        $plans=[];

        $plans["baselang_129"]=["Real World",129,[["Unlimited classes"],["Real World curriculum","This curriculum is what we recommend for most people. It's focused on preparing you for the real world - travel, dating, living in a Spanish-speaking country, friends, etc. It's a much heavier focus on speaking instead of reading and writing."]]];
        $plans["baselang_129_trial"]=$plans["baselang_129"];

        $plans["baselang_99"]=$plans["baselang_129"];
        $plans["baselang_99"][1]=99;
        $plans["baselang_99_trial"]=$plans["baselang_99"];

        $plans["baselang_149"]=$plans["baselang_129"];
        $plans["baselang_149"][1]=149;
        $plans["baselang_149_trial"]=$plans["baselang_149"];

        $plans["grammarless-online-1000paymentplan"]=["GrammarLess",250,[["Unlimited classes"],["Real World curriculum","This curriculum is what we recommend for most people. It's focused on preparing you for the real world - travel, dating, living in a Spanish-speaking country, friends, etc. It's a much heavier focus on speaking instead of reading and writing."]]];
        $plans["grammarless-online-900"]=["GrammarLess",900,[["Unlimited classes"],["Real World curriculum","This curriculum is what we recommend for most people. It's focused on preparing you for the real world - travel, dating, living in a Spanish-speaking country, friends, etc. It's a much heavier focus on speaking instead of reading and writing."]]];

        $plans["9zhg"]=$plans["baselang_129"];
        $plans["9zhg"][1]=999;

        $plans["baselang_dele"]=["DELE",199,[["Unlimited classes"],["DELE curriculum","This curriculum is for two types of people: those who need to prepare for the DELE exam from Instituto Cervantes, or those who prefer a more rigid, academic structure to systematically remove their weak points. There is also more focus on reading and writing.<br/>You get access to all of the DELE levels we have: A2-B1, B1-B2, B2-C1, and C1-C2."],["DELE-trained teachers","The teachers who teach our DELE program are NOT the same as our Real World teachers. They are specifically trained to teach DELE by a few Instituto Cervantes certified people on our team.<br/>These are often teachers who started in Real World that proved exceptionally good at teaching grammar."]]];
        $plans["baselang_dele_trial"]=$plans["baselang_dele"];
        $plans["baselang_dele_test"]=$plans["baselang_dele"];

        $plans["baselang_dele_realworld"]=["DELE + Real World",249,[["Unlimited classes"],["Both sets of teachers","Have favorite teachers from Real World you want to continue with, but want to test out the DELE program too? With this plan, you get access to both sets of teachers at a steep discount."],["Both curriculums","With this plan, you'll have access to both curriculums and sets of teachers, meaning you can jump between them as you'd like. By getting both programs at the same time, you get a steep discount over paying for both separately."]]];

        $plans["baselang_hourly"]=["Hourly",9,[]];
        $plans["baselang_hourly"][2][]=$plans["baselang_129"][2][1];
        $plans["baselang_hourly"][2][]=["Pay per class (one hour included)","With the Hourly plan, you pay by the hour instead of a flat rate for unlimited classes. One hour of class is included in the $9/mo subscription, and then it's $9 an hour for more classes. You can also buy credits in bulk for a lower hourly rate.<br />This is ideal for students who want to 'pause' their subscriptions while taking a break, or who don't have enough time to get the most out of unlimited but don't want to completely stop."];

        $plans["medellin_RW"]=["Medellin Real World",599,[["Unlimited online classes and in-person classes in Medellin Real World curriculum"],["Medellin Real World curriculum","This curriculum is what we recommend for most people. It's focused on preparing you for the real world - travel, dating, living in a Spanish-speaking country, friends, etc. It's a much heavier focus on speaking instead of reading and writing."]]];
        $plans["medellin_DELE"]=["Medellin DELE",699,[["Unlimited online classes and in-person classes in Medellin DELE curriculum"],["Medellin DELE curriculum","This curriculum is for two types of people: those who need to prepare for the DELE exam from Instituto Cervantes, or those who prefer a more rigid, academic structure to systematically remove their weak points. There is also more focus on reading and writing.<br/>You get access to all of the DELE levels we have: A2-B1, B1-B2, B2-C1, and C1-C2."],["DELE-trained teachers","The teachers who teach our DELE program are NOT the same as our Real World teachers. They are specifically trained to teach DELE by a few Instituto Cervantes certified people on our team.<br/>These are often teachers who started in Real World that proved exceptionally good at teaching grammar."]]];

        $plans["medellin_RW_Lite"] = [];
        $plans["medellin_RW_Lite"]=["Medellin Real World Lite",599,[["Upto 2 hours a day of in-person classes and unlimited online classes"], ["Real World curriculum","This curriculum is what we recommend for most people. It's focused on preparing you for the real world - travel, dating, living in a Spanish-speaking country, friends, etc. It's a much heavier focus on speaking instead of reading and writing."]]];
        
        $plans["medellin_RW_1199"] = [];
        $plans["medellin_RW_1199"]=["Medellin Real World",1199, [["Unlimited in-person classes and unlimited online classes"],["Real World curriculum","This curriculum is what we recommend for most people. It's focused on preparing you for the real world - travel, dating, living in a Spanish-speaking country, friends, etc. It's a much heavier focus on speaking instead of reading and writing."]]];

        return $plans;
    }

    /**
     * Add credits to subscription checking $credits collection
     * @param $subscription
     * @param bool $credits
     * @return bool
     */
    public static function checkCredits($subscription,$credits=false){
        try {

            if($subscription->plan->type!="hourly"){
                return true;
            }


            if(!$credits){
                $credits = Credits::where('subscription_id',$subscription->subscription_id);
            }

            $result = \ChargeBee_Subscription::retrieve($subscription->subscription_id);
            $credits = $credits->where('subscription_id',$subscription->subscription_id)->where('period',$result->subscription()->billingPeriod)->first();
            if(!$credits){
                $add = Credits::$default_credits;
                if($subscription->user->statistics->where('type','double_credits')->first()){
                    $add += Credits::$cancel_credits;
                }

                $subscription->user->credits+=$add;
                $subscription->user->secureSave();

                $credit = new Credits;
                $credit->user_id=$subscription->user->id;
                $credit->subscription_id=$subscription->subscription_id;
                $credit->period=$result->subscription()->billingPeriod;
                $credit->credits=$add;
                $credit->save();

                Error::reportInfo('Add Credits: '.$subscription->user->email.' count: '.$add.' now: '.$subscription->user->credits.' Trace: '.$credit->id);
            }
        } catch (\Exception $e){
            Error::reportError('Error Adding Credits on subscription',$e->getLine(),$e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Get Types and equivalence con planes
     * @return array of strings
     */
    public static function getCurrentTypes(){
        $types=[];
        $types['rw']='real';
        $types['hourly']='real';
        $types['dele']='dele';

        return $types;
    }

    /**
     * Convert Subscription type to classes type
     * @param $type
     * @return string
     */
    public static function getConvertedType($type='rw'){
        $types = Subscription::getCurrentTypes();
        if(!isset($types[$type])){
            return Subscription::$default_type;
        }

        return $types[$type];
    }

    /**
     * Before save the model, check fillable attributes to update model
     * @return Subscription
     */

    public function secureSave(){

        $fillables = [];

        foreach($this->fillable as $fillable){
            if(isset($this->toArray()[$fillable])){
                $fillables[$fillable]=$this->toArray()[$fillable];
            }
        };

        $subscription = Subscription::find($this->id);
        if($subscription){
            $subscription->update($fillables);
        } else {
            $subscription = Subscription::create($fillables);
            $this->id = $subscription->id;
        }

        return $this;
    }


    /**
     * Get Pause Options
     * @return array
     */
    public static function getPauseOptions(){
        $options = [];

        $options['P14D'] = '2 weeks';
        $options['P1M'] = '1 month';
        $options['P42D'] = '6 weeks';
        $options['P2M'] = '2 months';
        $options['P3M'] = '3 months';

        return $options;
    }

    /**
     * Get filters (used on dashboard menu) for ChargeBee
     * @return array
     */
    public static function getFilters(){
        $filters = [];
        $filters ['online_active'] = ['fields'=>[['name'=>'status','value'=>['in_trial','active','future']],['name'=>'planId','value'=>[Plan::getByLocation()->pluck('name')->toArray()]]],'results'=>0];

        $filters ['online_rw'] = ['fields'=>[['name'=>'planId','value'=>[Plan::getByLocationAndType()->pluck('name')->toArray()]]],'results'=>0];
        $filters ['online_rw_paused'] = ['fields'=>[['name'=>'status','value'=>['paused']],['name'=>'planId','value'=>[Plan::getByLocationAndType()->pluck('name')->toArray()]]],'results'=>0];
        $filters ['online_dele'] = ['fields'=>[['name'=>'planId','value'=>[Plan::getByLocationAndType('online','dele')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['online_dele_paused'] = ['fields'=>[['name'=>'status','value'=>['paused']],['name'=>'planId','value'=>[Plan::getByLocationAndType('online','dele')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['online_hourly'] = ['fields'=>[['name'=>'planId','value'=>[Plan::getByLocationAndType('online','hourly')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['online_hourly_paused'] = ['fields'=>[['name'=>'status','value'=>['paused']],['name'=>'planId','value'=>[Plan::getByLocationAndType('online','hourly')->pluck('name')->toArray()]]],'results'=>0];

        $filters ['medellin_active'] = ['fields'=>[['name'=>'status','value'=>['in_trial','active','future']],['name'=>'planId','value'=>[Plan::getByLocation('medellin')->pluck('name')->toArray()]]],'results'=>0];

        $filters ['medellin_rw_mo'] = ['fields'=>[['name'=>'currentTermStart','value'=>[gmdate("Y-m", strtotime("+1 months")).'-01 00:00:00'],'operation'=>'<='],['name'=>'currentTermStart','value'=>[gmdate('Y-m').'-01 00:00:00','operation'=>'>=']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','rw')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['medellin_rw_wk'] = ['fields'=>[['name'=>'currentTermStart','value'=>[gmdate('Y-m-d', strtotime('-'.date('w').' days')).' 00:00:00'],'operation'=>'>='],['name'=>'currentTermStart','value'=>[gmdate('Y-m-d', strtotime('+'.(7-date('w')).' days')).' 00:00:00','operation'=>'<=']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','rw')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['medellin_rw_paused'] = ['fields'=>[['name'=>'status','value'=>['paused']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','rw')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['medellin_rw_start_soon'] = ['fields'=>[['name'=>'currentTermStart','value'=>['name'=>'currentTermStart','value'=>[gmdate("Y-m", strtotime("+1 months")).'-01 00:00:00'],'operation'=>'<=']],['name'=>'currentTermStart','value'=>['name'=>'currentTermStart','value'=>[gmdate("Y-m-d H:i:s")],'operation'=>'>=']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','rw')->pluck('name')->toArray()]]],'results'=>0];


        $filters ['medellin_dele_mo'] = ['fields'=>[['name'=>'currentTermStart','value'=>[gmdate("Y-m", strtotime("+1 months")).'-01 00:00:00'],'operation'=>'<='],['name'=>'currentTermStart','value'=>[gmdate('Y-m').'-01 00:00:00','operation'=>'>=']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','dele')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['medellin_dele_wk'] = ['fields'=>[['name'=>'currentTermStart','value'=>[gmdate('Y-m-d', strtotime('-'.date('w').' days')).' 00:00:00'],'operation'=>'>='],['name'=>'currentTermStart','value'=>[gmdate('Y-m-d', strtotime('+'.(7-date('w')).' days')).' 00:00:00','operation'=>'<=']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','dele')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['medellin_dele_paused'] = ['fields'=>[['name'=>'status','value'=>['paused']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','dele')->pluck('name')->toArray()]]],'results'=>0];
        $filters ['medellin_dele_start_soon'] = ['fields'=>[['name'=>'currentTermStart','value'=>['name'=>'currentTermStart','value'=>[gmdate("Y-m", strtotime("+1 months")).'-01 00:00:00'],'operation'=>'<=']],['name'=>'currentTermStart','value'=>['name'=>'currentTermStart','value'=>[gmdate("Y-m-d H:i:s")],'operation'=>'>=']],['name'=>'planId','value'=>[Plan::getByLocationAndType('medellin','dele')->pluck('name')->toArray()]]],'results'=>0];

        $filters ['online_rw_free_days'] = ['local'=>true,'results'=>0,'type'=>'free_days','data'=>['location'=>'online','type'=>'rw','current'=>true]];
        $filters ['online_dele_free_days'] = ['local'=>true,'results'=>0,'type'=>'free_days','data'=>['location'=>'online','type'=>'dele','current'=>true]];
        $filters ['online_hourly_free_days'] = ['local'=>true,'results'=>0,'type'=>'free_days','data'=>['location'=>'online','type'=>'hourly','current'=>false]];
        $filters ['medellin_rw_free_days'] = ['local'=>true,'results'=>0,'type'=>'free_days','data'=>['location'=>'medellin','type'=>'rw','current'=>true]];
        $filters ['medellin_dele_free_days'] = ['local'=>true,'results'=>0,'type'=>'free_days','data'=>['location'=>'medellin','type'=>'dele','current'=>true]];

        $filters ["medellin_sm_active"] = ['local'=>true,'results'=>0,'type'=>'immersion','data'=>['current'=>true]];
        $filters ["medellin_sm_start_soon"] = ['local'=>true,'results'=>0,'type'=>'immersion','data'=>['current'=>false]];

        return $filters;
    }

    /**
     * Create a new standard subscription, Default RW and Last Plan Price
     * @param boolean $is_rw
     * @return Subscription
     */
    public static function getDefaultSubscription($is_rw=true){
        $subscription = new Subscription;
        $subscription->plan=Plan::getDefaultPlan($is_rw);
        $subscription->plan_name=$subscription->plan->plan_id;
        $subscription->ends_at=gmdate('Y-m-d').' 00:00:00';
        $subscription->starts_at=gmdate("Y-m-d H:i:s", strtotime("-1 months"));
        $subscription->status='cancelled';
        $subscription->location='online';
        $subscription->next_billing=gmdate('Y-m-d').' 00:00:00';
        $subscription->period_unit='month';
        return $subscription;
    }


    /**
     * relation get current user who have this subscription
     * @return Plan | BelongsTo
     */

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan','plan_name','plan_id');
    }


    /**
     * relation get current user who have this subscription
     * @return Plan | BelongsTo
     */

    public function future()
    {
        return $this->belongsTo('App\Models\Plan','change','plan_id');
    }

    /**
     * relation get current user who have this subscription
     * @return User | BelongsTo
     */

    public function user()
    {
        return $this->belongsTo('App\User');
    }


}
