<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inmersion extends Model
{
    protected $table = 'inmersions';

    protected $fillable = [
        'user_id',
        'teacher_id',
        'start_week',
        'week_end',
        'hour_format'
    ];

    public function student()
    {
        return $this->hasOne('App\User','id','user_id');
    }

    public function teacher()
    {
        return $this->hasOne('App\User','id','teacher_id');
    }
}
