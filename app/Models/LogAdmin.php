<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAdmin extends Model
{
	protected $table = 'log_admins';

    protected $fillable = [
    	'user_id',
        'admin_mail',
        'field',
        'old_data',
        'new_data',
    ];

    public function user()
    {
        return $this->hasOne('App\User','id','user_id');
    }
}
