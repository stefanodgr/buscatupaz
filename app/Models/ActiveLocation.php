<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveLocation extends Model
{
	protected $table = 'active_locations';

    protected $fillable = [
        'user_id',
        'activation_day',
        'date_to_schedule',
        'trial_payday',
        'plan',
        'price',
        'new_student'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
