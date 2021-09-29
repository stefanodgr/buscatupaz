<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyPrebook extends Model
{
	protected $table = 'buy_prebook';

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'hours',
        'status',
        'activation_date',
    ];

    public function student()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
