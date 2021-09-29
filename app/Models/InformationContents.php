<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformationContents extends Model
{
    protected $table = 'information_contents';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'order',
        'state',
        'description',
        'information_content_id',
        'location_id'
    ];
}
