<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyInmersion extends Model
{
	protected $table = 'buy_inmersions';

    protected $fillable = [
        'user_id',
        'teacher_id',
        'total_price',
        'inmersion_start',
        'inmersion_end',
        'hour_format',
        'second_payment_date',
        'status',
        'location_id'
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
