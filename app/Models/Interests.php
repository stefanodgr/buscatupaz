<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interests extends Model
{

    protected $table = 'interests';

    protected $fillable = [
        'title'
    ];


    public function users()
    {
        return $this->belongsToMany('App\User','users_interests','users_interests','user_id','interest_id');
    }


}