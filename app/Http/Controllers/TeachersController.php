<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\MiniBlog;
use App\Models\Role;
use App\Models\Statistics;
use App\Models\UserCalendar;
use App\Models\UserEvaluation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class TeachersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "teachers");
    }

    public function saveTeacherFavorite(Request $request){
        $teacher_id=$request->input("teacher_id");
        $user = User::getCurrent();
        $limitDate=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval('P7D'));

        if(!isset($user->favorite_teacher_time) || (isset($user->favorite_teacher_time) && $user->favorite_teacher_time<=gmdate("Y-m-d"))){
            User::where("id",$user->id)->update(["favorite_teacher_time"=>$limitDate->format("Y-m-d"),"favorite_teacher"=>$teacher_id]);

            Statistics::create(["user_id"=>$user->id,"type"=>"Favorite_teacher","data_x"=>$teacher_id,"data_y"=>"favorite_teacher"]);

            return redirect()->route("teachers")->with(["message_info"=>"Your favorite teacher has been saved"]);
        }


        return redirect()->route("teachers")->withErrors(["An error has occurred saving your favorite teacher. "]);

    }

    public function saveTeacherEvaluation(Request $request){
        $data=$request->only(["evaluation","teacher_id"]);
        $user = User::getCurrent();

        $evaluation=UserEvaluation::where("user_id",$user->id)->where("teacher_id",$data["teacher_id"])->first();

        if(!$evaluation){
            $evaluation=UserEvaluation::create(["user_id"=>$user->id,"teacher_id"=>$data["teacher_id"],"evaluation"=>$data["evaluation"]]);
        } else {
            UserEvaluation::where("user_id",$user->id)->where("teacher_id",$data["teacher_id"])->update(["evaluation"=>$data["evaluation"]]);
        }

        $teacherEvaluations=UserEvaluation::where("teacher_id",$data["teacher_id"])->get();
        $teacherProm=0;
        foreach ($teacherEvaluations as $teacherEvaluation){
            $teacherProm+=$teacherEvaluation->evaluation;
        }


        $teacherProm/=count($teacherEvaluations);
        User::where("id",$data["teacher_id"])->update(["evaluation"=>$teacherProm]);

        Statistics::create(["user_id"=>$user->id,"type"=>"Evaluation_teacher","data_x"=>$data["teacher_id"],"data_y"=>$data["evaluation"]]);

        return response()->json(['success' => true]);
    }

    public function getTeacherList(Request $request){
        $user = User::getCurrent();
        $route = \Route::currentRouteName();
        $get_teachers_school = false;
        if($route=="get_teachers_school"){
            if($user->location_id){
                $get_teachers_school = true;
                Log::info("User: ".$user->id." consulting school teachers");
            }
        }

        $filters=$request->only(["gender","teaching_style","strongest_with","english_level"]);
        $interests=$request->input(["filter_interests"]);
        $first_name=$request->get("first_name");

        $filters=array_filter($filters);

        if($first_name){
            if($get_teachers_school) {
                $teachers = collect();
                $verify_teachers = Role::where('name','teacher')->first()->users()->where('activated',1)->where('first_name','LIKE','%'.ucwords(strtolower($first_name)).'%')->orderBy('first_name','asc')->get();

                foreach($verify_teachers as $teacher) {
                    if($teacher->hasLocation($user->location_id)) {
                        $teachers->push($teacher);
                    }
                }
            }else {
                $teachers = Role::where('name','teacher')->first()->users()->where('activated',1)->where('first_name','LIKE','%'.ucwords(strtolower($first_name)).'%')->orderBy('first_name','asc')->get();
            }  
        }
        else{
            if($get_teachers_school) {
                $teachers = collect();
                $verify_teachers = Role::where('name','teacher')->first()->users()->where('activated',1)->orderBy('first_name','asc')->get();

                foreach($verify_teachers as $teacher) {
                    if($teacher->hasLocation($user->location_id)) {
                        $teachers->push($teacher);
                    }
                }
            }else {
                $teachers = Role::where('name','teacher')->first()->users()->where('activated',1)->orderBy('first_name','asc')->get();
            } 
        }

        foreach ($filters as $k=>$filter){
            $teachers = $teachers->where($k,$filter);
        }

        $subscriptionType=session("current_subscription");

        $teachers=($subscriptionType=="real")?$teachers->where("is_deleteacher",0):$teachers->where("is_deleteacher",1);
        //$teachers=$teachers->get();

        if($interests){

            foreach($teachers as $k=>$teacher){
                $teacherInterests=($teacher->interests()->whereIn("title",$interests)->first());
                if(!$teacherInterests){
                    $teachers->forget($k);
                }
            }
        }

        return view("teachers.includes.teachers",["teachers"=>$teachers]);
    }

    public function getTeachers(){
        $user = User::getCurrent();
        
        $menu_active = "teachers";
        $route = \Route::currentRouteName();
        $teachers_school = false;

        if($route=="teachers_school"){
            if($user->location_id){
                $menu_active = "teachers_school";
                $teachers_school = true;
                Log::info("User: ".$user->id." entering school teachers");
            }else {
                return redirect()->route('teachers');
            }
        }

        $subscriptionType=session("current_subscription");

        if($teachers_school) {
            $teachers = collect();
            $verify_teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->get();

            foreach($verify_teachers as $teacher) {
                if($teacher->hasLocation($user->location_id)) {
                    $teachers->push($teacher);
                }
            }
        }else {
            $teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->get();
        }

        if($subscriptionType=="real"){
            $teachers=$teachers->where("is_deleteacher",0);
        } else {
            $teachers=$teachers->where("is_deleteacher",1);
        }

        $interests=collect();
        foreach($teachers as $teacher){
            $interests=$interests->merge($teacher->interests);
        }

        $interests=$interests->unique('id');

        return view("teachers.teachers",["menu_active"=>$menu_active, "interests"=>$interests]);
    }

    public function getStudentInfo($id){
        $student = User::where("id",$id)->first();


        return view("calendar.includes.student_profile",["student"=>$student]);
    }

    public function getNotesList($user_id,$skip=0,$pages=1){
        $take=5*$pages;
        $showMore=false;

        $notes=MiniBlog::orderBy("created_at","desc")->where("user_id",$user_id)->skip($skip)->take($take)->get();
        $firstNote=MiniBlog::orderBy("created_at","asc")->where("user_id",$user_id)->first();
        $firstInNotes=$notes->last();

        if($firstNote){
            if($firstNote->id!=$firstInNotes->id){
                $showMore=true;
            }
        }

        return view("students.includes.notes",["notes"=>$notes,"showMore"=>$showMore]);
    }

    public function saveNote(Request $request){
        $teacher=User::getCurrent();
        $user_id=$request->get("user_id");
        $description=$request->get("description");

        $note=MiniBlog::create(["user_id"=>$user_id,"teacher_id"=>$teacher->id,"description"=>$description]);
        
        return response()->json(['Nota' => $note]);
    }

    public function updateNote(Request $request){
        $note_id=$request->get("note_id");
        $description=$request->get("description");

        MiniBlog::where("id",$note_id)->update(["description"=>$description]);
        
        return response()->json(['Nota' => "Success"]);
    }

}
