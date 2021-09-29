<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
	protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'feedback'
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
