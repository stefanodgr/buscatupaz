<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledChanges extends Model
{
    protected $table = 'scheduled_plans';
    protected $fillable = [
        'user_id',
        'plan',
        'change_date',
        'status'
    ];

    public function referred()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}