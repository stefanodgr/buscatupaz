<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Credits extends Model
{

    protected $table = 'credits';
    public static $default_credits = 2;
    public static $cancel_credits = 2;


    protected $fillable = [
        'user_id',
        'subscription_id',
        'period',
        'credits',
    ];


    /**
     * Get default of 1 credit
     * @return int
     */
    public static function getDefaultPrice(){
        return Credits::getPrice(1);
    }

    public static function calculate($Q){
        return self::getPrice($Q)*$Q;
    }

    public static function getPrice($Q){
        if($Q<=15){
            return 4.5;
        } elseif($Q<=30){
            return 4;
        } elseif($Q<=45){
            return 3.5;
        } else {
            return 3;
        }
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }


}