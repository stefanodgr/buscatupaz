<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TokenReset extends Model
{



    protected $table = 'password_reset';

    protected $hashKey='kWjFioOmqNraKJWtncef';

    protected $fillable = [
        'email',
        'token'
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->token = $this->createToken();
    }

    public function createToken(){
        return hash_hmac('sha256', str_random(40), $this->hashKey);
    }

}