<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{

    protected $table = 'levels';

    protected $fillable = [
        'name',
        'slug',
        'course_id',
        'enabled',
        'level_order',
        'type',
        'meta_title',
        'includes_options',
        'youtube_link',
        'description',
        'desc_sales',
        'desc_included',
        'desc_whofor',
        'price'
    ];

    public function countIncludedOptions(){
        $included_options = count($this->getIncludedOptions());


        return ($included_options);
    }
    public function getIncludedOptions(){
        if($this->includes_options){
            return json_decode($this->includes_options);
        }
        return false;
    }
    public function getYoutubeCode(){
        if($this->youtube_link){
            parse_str( parse_url( $this->youtube_link, PHP_URL_QUERY ), $my_array_of_vars );
            return $my_array_of_vars['v'];
        }
        return false;
    }

    public function lessons()
    {
        return $this->hasMany('App\Models\Lesson')->orderBy("order","asc");
    }


    public function options(){
        return $this->hasMany('App\Models\LevelOptions');
    }

}