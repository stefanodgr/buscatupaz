<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFreeDays extends Model
{
    protected $table = 'users_free_days';
    protected $fillable = [
        'user_id',
        'referred_id',
        'free_days',
        'active',
        'available',
        'claimed',
        'admin',
        'ref_activation_date'
    ];

    public function referred()
    {
        return $this->hasOne('App\User','id','referred_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}