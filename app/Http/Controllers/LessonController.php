<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Level;
use App\Models\Statistics;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class LessonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "core_lessons");
    }

    public function getLevel($type,$level_slug){
        $user = User::getCurrent();
        $subscriptionType=$user->getCurrentSubscriptionType();

        if($subscriptionType=="dele_real"){
            $level=Level::where("slug",$level_slug)->where("enabled",1)->first();
            if($type!=$level->type){
                return redirect()->route("level",["type"=>$level->type,"level_slug"=>$level_slug]);
            }
            if($level->type=="real"){
                session(['current_subscription' => $level->type]);
            } else {
                session(['current_subscription' => "dele"]);
            }

        } else {
            $level=Level::where("slug",$level_slug)->where("type",$type)->where("enabled",1)->first();
            if(!$level || $type!=$level->type){
                return redirect()->route("level",["type"=>$level->type,"level_slug"=>$level_slug]);
            }
        }

        if(!$level){
            return redirect()->route("lessons");
        }


        foreach($level->lessons->where("enabled",1) as &$lesson){
            $lesson->completed=false;
            $lesson_completed=$user->lessons()->where("lesson_id",$lesson->id)->where("completed",1)->first();
            if($lesson_completed){
                $lesson->completed=true;
            };

        }

        $menu_active="core_lessons";
        if($type!="real"){
            $menu_active=$type;
        }

        if($type=="sm"){
            $menu_active="sm_lessons";
        }

        return view("lessons.level",["level"=>$level,"breadcrumb"=>true,"menu_active"=>$menu_active]);
    }

    public function saveLesson(Request $request){
        $user = User::getCurrent();
        $lesson_id=$request->input("lesson");

        $lesson = Lesson::where("id",$lesson_id)->where("enabled",1)->first();
        if(!$lesson){
            return response()->json(['status' => 'sucess', 'lesson_state' => 0]);
        }

        $user_lesson = $user->lessons()->where("lesson_id",$lesson_id)->first();


        $statistics=Statistics::where("user_id",$user->id)->where("type","Complete_Lesson")->where("data_x",$lesson->id)->first();
        if(!$statistics){
            Statistics::create(["user_id"=>$user->id,"type"=>"Complete_Lesson","data_x"=>$lesson->id,"data_y"=>$lesson->level->type]);
        } else {
            Statistics::where("id",$statistics->id)->update(["type"=>"Complete_Lesson"]);
        }

        if(!$user_lesson){
            $user->lessons()->save($lesson);
            $user->lessons()->updateExistingPivot($lesson_id,["completed"=>1,"finished_at"=>gmdate("Y-m-d H:i:s")]);

            return response()->json(['status' => 'sucess', 'lesson_state' => 1]);
        } else {
            if($user_lesson->pivot->completed){

                Statistics::create(["user_id"=>$user->id,"type"=>"Complete_Lesson","data_x"=>$user->user_level,"data_y"=>"0"]);

                $user->lessons()->updateExistingPivot($lesson_id,["completed"=>0,"finished_at"=>gmdate("Y-m-d H:i:s")]);
                return response()->json(['status' => 'sucess', 'lesson_state' => 0]);
            } else {
                $user->lessons()->updateExistingPivot($lesson_id,["completed"=>1,"finished_at"=>gmdate("Y-m-d H:i:s")]);
                return response()->json(['status' => 'sucess', 'lesson_state' => 1]);
            }

        }

    }

    public function getLesson($type,$level_slug,$lesson_slug){
        $user = User::getCurrent();
        $subscriptionType=$user->getCurrentSubscriptionType();

        if($subscriptionType=="dele_real"){
            $level=Level::where("slug",$level_slug)->where("enabled",1)->first();
            if($level->type=="real"){
                session(['current_subscription' => $level->type]);
            } else {
                session(['current_subscription' => "dele"]);
            }

            if($type!=$level->type){
                return redirect()->route("lesson",["type"=>$level->type,"level_slug"=>$level_slug,"lesson_slug"=>$lesson_slug]);
            }
        } else {
            $level=Level::where("slug",$level_slug)->where("type",$type)->where("enabled",1)->first();
            if($type!=$level->type){
                return redirect()->route("lesson",["type"=>$level->type,"level_slug"=>$level_slug,"lesson_slug"=>$lesson_slug]);
            }
        }

        if(!$level){
            return redirect()->route("lessons");
        }

        $lesson=$level->lessons()->where("slug",$lesson_slug)->first();
        if(!$lesson){
            return redirect()->route("level",["level_slug"=>$level_slug]);
        }

        $user_lesson = $user->lessons()->where("lesson_id",$lesson->id)->first();

        $menu_active="core_lessons";
        if($type!="real"){
            $menu_active=$type;
        }

        if($type=="sm"){
            $menu_active="sm_lessons";
        }

        return view("lessons.lesson",["level"=>$level,"lesson"=>$lesson,"user_lesson"=>$user_lesson,"breadcrumb"=>true,"menu_active"=>$menu_active]);

    }

    public function saveHomework(Request $request){
        $user = User::getCurrent();

        $data=$request->only(["lesson","text_homework"]);
        $lesson_id=$data["lesson"];

        $lesson=Lesson::where("id",$lesson_id)->where("enabled",1)->first();
        if(!$lesson){
            return response()->json(['status' => 'sucess']);
        }

        if(isset($request->audio_data)){
            if(!file_exists(public_path().'/assets/homeworks/user_audio/'.$lesson_id."_".$user->id.'.wav')){
                $request->audio_data->storeAs('assets/homeworks/user_audio', $lesson_id."_".$user->id.'.wav', 'uploads');
            }
        }

        if(!empty($data["text_homework"])){
            $user_lesson=$user->lessons()->where("lesson_id",$lesson_id)->first();
            if(!$user_lesson){

                $user->lessons()->save($lesson);
                $user->lessons()->updateExistingPivot($lesson_id,["homework"=>$data["text_homework"]]);
            } elseif(!isset($user_lesson->pivot->homework)){
                $user->lessons()->updateExistingPivot($lesson_id,["homework"=>$data["text_homework"]]);
            };

        }

        return response()->json(['status' => 'sucess']);
    }

    public function getLessons($type=false){
        $user = User::getCurrent();
        $subscriptionType=session("current_subscription");

        $route = \Route::currentRouteName();
        if($route=="sm_lessons"){
            return redirect()->route("lessons_type",["type"=>"sm"]);
        }else{
            if($subscriptionType=="dele" && (!$type || !in_array($type,["intros","grammar","skills","test","sm"]))){
                return redirect()->route("lessons_type",["type"=>"grammar"]);
            } elseif(($subscriptionType=="real" && $type!="real" && $type!="sm") || !$type){
                return redirect()->route("lessons_type",["type"=>"real"]);
            }
        }

        $levels = Level::where("type",$type)->where("enabled",1)->orderBy("level_order","asc")->get();
        foreach($levels as $level){
            $level_ids[] = $level->id;
        }

        $lesson_ids = array();
        $lessonsids_levelids_mapping = array();
        $level_lessons = Lesson::whereIn("level_id",$level_ids)->where("enabled",1)->get();
        $user_lessons_mapping = array();
        foreach($level_lessons as $level_lesson) {
            $lesson_ids[] = $level_lesson->id;
            $lessonsids_levelids_mapping[$level_lesson->id] = $level_lesson->level_id;
        }

        $user_lessons_mapping = array();
        $user_lessons = $user->lessons()->whereIn("lesson_id",$lesson_ids)->where("completed",1)->get();
        foreach($user_lessons as $user_lesson) {
            $level_id = $lessonsids_levelids_mapping[$user_lesson->id];
            if(array_key_exists($level_id, $user_lessons_mapping)){
                $user_lesson_array = $user_lessons_mapping[$level_id]; 
                array_push($user_lesson_array, $user_lesson);
                $user_lessons_mapping[$level_id] = $user_lesson_array;
            } else{
                $user_lesson_array = array();
                array_push($user_lesson_array, $user_lesson);
                $user_lessons_mapping[$level_id] = $user_lesson_array;
            }
        }

        foreach($levels as &$level){
            if(sizeof($user_lessons_mapping)>0 && array_key_exists($level->id, $user_lessons_mapping)) {
                $level->completed = sizeof($user_lessons_mapping[$level->id]);
            } else {
                $level->completed = 0;
            }
        };

        $menu_active="core_lessons";
        if($type!="real"){
            $menu_active=$type;
        }

        if($type=="sm"){
            $menu_active="sm_lessons";
        }

        return view("lessons.lessons",["levels"=>$levels,"menu_active"=>$menu_active,"type"=>$type]);
    }

}
