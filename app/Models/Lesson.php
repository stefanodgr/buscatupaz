<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{

    protected $table = 'lessons';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'enabled',
        'options',
        'level_id',
        'order',
        'homework_audio',
        'homework_text',
        'externalurl',
        'is_free'
    ];

    public function hasAudioHomework(){
        if($this->homework_audio){
            return true;
        };
        return false;
    }

    public function hasTextHomework(){
        if($this->homework_text){
            return true;
        };
        return false;
    }

    public function hasHomeworks(){
        if($this->hasTextHomework() || $this->hasAudioHomework()){
            return true;
        }
        return false;
    }

    public function user()
    {
        return $this->belongsToMany('App\User','users_lessons')->withPivot("completed","finished_at","homework");
    }

    public function level()
    {
        return $this->belongsTo('App\Models\Level');
    }

}