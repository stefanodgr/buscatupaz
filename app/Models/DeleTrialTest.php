<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeleTrialTest extends Model
{
	protected $table = 'baselang_dele_trial_test';

    protected $fillable = [
        'user_id',
        'completed',
        'ends_at_last_subscription'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
