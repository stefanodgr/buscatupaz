<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;


class UserCancellation extends Model
{

    protected $table = 'users_cancellation';

    protected $fillable = [
        'user_id',
        'reason',
        'reason_id',
        'other',
    ];


    /*
     * Relation to know reason of the cancellation
     * */

    public function reason()
    {
        return $this->belongsTo('App\Models\CancellationReason','reason_id','id');
    }

    /*
     * Relation to know user who did the cancellation
     * */

    public function user()
    {
        return $this->belongsTo('App\User');
    }


}