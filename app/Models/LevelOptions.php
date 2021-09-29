<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelOptions extends Model
{

    protected $table = 'levels_options';

    protected $fillable = [
        'title',
        'description',
        'lesson_id'
    ];

}