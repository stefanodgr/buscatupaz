<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Product extends Model
{
	protected $table = 'products';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'activation_date',
        'product',
        'transaction',
        'limit_date',
        'discount',
        'extra'
    ];


    public function prebookSlots(){
        if($this->type=='gold'){
            return 15;
        }
        return 5;
    }

    protected $casts = [
        'extra' => 'array',
    ];

    protected $dates =[
        'created_at','updated_at','activation_date','limit_date'
    ];

    /**
     * relation get current user who have this subscription
     * @return Location | BelongsTo
     */

    public function location()
    {
        return $this->belongsTo('App\Models\Location');
    }


    public function student()
    {
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function teacher()
    {
        return $this->hasOne('App\Models\User','id','teacher_id');
    }
}
