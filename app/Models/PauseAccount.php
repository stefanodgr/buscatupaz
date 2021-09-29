<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PauseAccount extends Model
{
	protected $table = 'pause_account';

    protected $fillable = [
        'user_id',
        'activation_day',
        'token'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
