<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Plan extends Model
{

    protected $table = 'plans';
    protected $default = 'baselang_149';

    protected $fillable = [
        'type',
        'plan_id',
        'name',
        'display_name',
        'price',
        'features',
        'location_name',
        'current_subscription',
        'unlimited_classes',
        'can_pause',
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     * @var array
     */
    protected $casts = [
        'features' => 'array',
    ];

    protected $dates = [
        'created_at','updated_at'
    ];

    /**
     * @param string $type possible values 'rw','dele','hourly'
     * @return Collection
     */
    public static function getByType($type='rw'){
        $plans = Plan::where('type',$type)->get();
        return $plans;
    }

    public static function getByPlanId($plan_id='baselang_149'){
        $plans = Plan::where('plan_id',$plan_id)->get();
        return $plans;
    }

    public static function getActivePlans(){
        return Plan::where('status',1)->get();
    }


    /**
     * @param string $location possible values 'online','medellin'
     * @return Collection
     */
    public static function getByLocation($location='online'){
        $plans = Plan::where('location_name',$location)->get();
        return $plans;
    }

    /**
     * @param string $location possible values 'online','medellin'
     * @param string $type possible values 'rw','dele','hourly'
     * @return Collection
     */
    public static function getByLocationAndType($location='online',$type='rw'){
        $plans = Plan::where('location_name',$location)->where('type',$type)->get();
        return $plans;
    }

    /**
     * @void
     */
    public static function refreshChargeBee(){
        $all = \ChargeBee_Plan::all([
            "limit" => 100,
            "status[is]" => "active"
        ]);
        Plan::truncate();
        foreach($all as $entry){
            $plan = new Plan();
            $plan->plan_id=$entry->plan()->id;
            $plan->name=$entry->plan()->name;
            $plan->price=$entry->plan()->price/100;

            if(isset($entry->plan()->metaData['name'])){
                $plan->display_name=$entry->plan()->metaData['name'];
            } else {
                $plan->display_name=$entry->plan()->name;
            }
            if(isset($entry->plan()->metaData['location'])){
                $plan->location_name=$entry->plan()->metaData['location'];
            }
            if(isset($entry->plan()->metaData['type'])){
                $plan->type=$entry->plan()->metaData['type'];
            }
            if(isset($entry->plan()->metaData['features'])){
                $plan->features=$entry->plan()->metaData['features'];
            }

            if(isset($entry->plan()->metaData['unlimited_classes'])){
                $plan->unlimited_classes=$entry->plan()->metaData['unlimited_classes'];
            }

            if(isset($entry->plan()->metaData['can_pause'])){
                $plan->can_pause=$entry->plan()->metaData['can_pause'];
            }

            if(isset($entry->plan()->metaData['status'])){
                $plan->status=$entry->plan()->metaData['status'];
            }

            $plan->save();
        }
    }

    /**
     * relation get current user who have this subscription
     * @return Location | BelongsTo
     */

    public function location()
    {
        return $this->belongsTo('App\Models\Location','location_name','name');
    }

    /**
     * Return Default Plan
     * @param boolean $is_rw Return default RW else DELE
     * @return Plan | Model
     */
    public static function getDefaultPlan($is_rw=true){
        $plan = new Plan;
        if(!$is_rw){
            $plan->default='baselang_dele';
        }
        $plan = Plan::where('name',$plan->default)->first();

        return $plan;
    }

    /**
     * Return Hourly Plan
     * @return Plan | Model
     */
    public static function getHourlyPlan(){
        $plan = Plan::where('name','baselang_hourly')->first();

        return $plan;
    }

    /**
     * relation get current subscriptions with this plan
     * @return Subscription | HasMany
     */

    public function subscriptions()
    {
        return $this->hasMany('App\Models\Subscription','plan_name','name');
    }

    /**
     * relation get current subscriptions with this plan
     * @return Subscription | HasMany
     */

    public function future_subscriptions()
    {
        return $this->hasMany('App\Models\Subscription','change','name');
    }

}