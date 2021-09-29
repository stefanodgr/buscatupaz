<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InmersionPayment extends Model
{
    protected $table = 'inmersion_payments';

    protected $fillable = [
        'user_id',
        'user_registration_day'
    ];

    public function student()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
