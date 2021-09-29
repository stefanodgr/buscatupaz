<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockDay extends Model
{
	protected $table = 'block_days';

    protected $fillable = [
        'teacher_id',
        'blocking_day',
        'from',
        'till',
        'public_holiday'
    ];

    public function teacher()
    {
        return $this->hasOne('App\User','id','teacher_id');
    }
}
