<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCalendar extends Model
{

    protected $table = 'users_calendar';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'from',
        'till',
        'day'
    ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }


}