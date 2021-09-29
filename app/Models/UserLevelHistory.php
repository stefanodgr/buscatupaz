<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLevelHistory extends Model
{

    protected $table = 'users_level_history';

    protected $fillable = [
        'user_id',
        'last_level',
        'new_level',
    ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }


}