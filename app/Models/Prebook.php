<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prebook extends Model
{
	protected $table = 'prebook';

    protected $fillable = [
        'user_id',
        'teacher_id',
        'day',
        'hour',
        'type',
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
