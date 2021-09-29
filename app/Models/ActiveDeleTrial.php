<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveDeleTrial extends Model
{
	protected $table = 'active_dele_trial';

    protected $fillable = [
        'user_id',
        'activation_day',
        'active_dele_trial',
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
