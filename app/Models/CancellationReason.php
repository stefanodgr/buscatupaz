<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;


class CancellationReason extends Model
{

    protected $table = 'cancellation_reasons';

    protected $fillable = [
        'title',
        'youtube',
        'description',
        'feedback',
        'pages',
        'link',
        'option',
        'status',
    ];

    /**
     * The attributes that should be casted to native types.
     * @var array
     */
    protected $casts = [
        'pages' => 'array',
    ];


}