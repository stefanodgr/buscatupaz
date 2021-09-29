<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'name',
        'timezone',
        'time_message',
       	'email_message',
       	'survey',
        'price'
    ];

    public function teachers(){
        return $this->belongsToMany('App\User','users_location');
    }

    public function plans(){
        return $this->belongsToMany('App\User','users_location');
    }
}
