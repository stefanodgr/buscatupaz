<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockDayLogs extends Model
{
	protected $table = 'block_days_logs';

    protected $fillable = [
        'admin_id',
        'block_day_id',
        'action',
        'old_data',
        'new_data'
    ];
    
}