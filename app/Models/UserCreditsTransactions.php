<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditsTransactions extends Model
{

    protected $table = 'users_credits_transactions';

    protected $fillable = [
        'chargebee_id',
        'user_id'
    ];



}