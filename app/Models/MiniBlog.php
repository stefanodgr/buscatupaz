<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniBlog extends Model
{
	protected $table = 'mini_blog';

    protected $fillable = [
        'user_id',
        'teacher_id',
        'description',
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