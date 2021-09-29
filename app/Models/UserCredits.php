<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCredits extends Model
{

    protected $table = 'users_hourly_credits';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'billing_cycle',
        'credits',
    ];


    public static function getCreditsPrice($Q){
        if($Q<=15){
            return 9;
        } elseif($Q<=30){
            return 8;
        } elseif($Q<=45){
            return 7;
        } else {
            return 6;
        }
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }


}