<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{

    protected $table = 'statistics';

    protected $fillable = [
        'type',
        'data_x',
        'data_y',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function teacherScoreCsv(){
        $subscriptions = Subscription::whereIn('status',['active','non_renewing','paused','in_trial'])->get();
        $score=[];
        foreach($subscriptions as $subscription){
            if($subscription->user->activated && $subscription->user->favorite_teacher){
                if(!isset($score[$subscription->user->favorite_teacher])){
                    $score[$subscription->user->favorite_teacher]=0;
                };
                $score[$subscription->user->favorite_teacher]++;
            }
        }

        $teachers = User::whereIn('id',array_keys($score))->get();
        foreach($teachers as &$teacher){
            $teacher->score=$score[$teacher->id];
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($teachers, ['first_name','last_name','email','score'])->download();
    }

    public static function teacherRecordsCsv(){
        $statistics=Statistics::where("type","Favorite_teacher")->get()->groupBy('data_x');
        $teachers=new Collection;
        foreach($statistics as $k=>$statistic) {
            $teacher = User::where("id",$k)->first();
            if($teacher && $teacher->activated){
                $teacher->score=count($statistic);
                $teachers->add($teacher);
            }

        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($teachers, ['first_name','last_name','email','score'])->download();
    }

    public static function subscriptionStatusCsv(){
        $students = Role::where('name','student')->first()->users->where("activated",1)->sortBy("first_name");

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($students, ['first_name','last_name','email','subscription.plan_name','subscription.status'])->download();
    }

}
