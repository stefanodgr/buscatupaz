<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEvaluation extends Model
{

    protected $table = 'users_evaluation';

    protected $fillable = [
        'evaluation',
        'user_id',
        'teacher_id',
    ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }


}