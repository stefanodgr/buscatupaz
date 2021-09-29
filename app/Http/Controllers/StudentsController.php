<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\UserCalendar;
use App\Models\UserEvaluation;
use App\Models\UserLevelHistory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class StudentsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "students");
    }

    public function getStudentList(){
        $user = User::getCurrent();

        $classes=Classes::orderBy("class_time","desc")->where("teacher_id",$user->id)->groupBy("user_id")->get();

        $students=[];
        foreach($classes as $class){
            if($class->student){
                $students[]=$class->student;
            }
        }


        return view("students.includes.students",["students"=>$students]);
    }

    public function studentDownLevel(Request $request){
        $student = $request->get("student_id");
        $student = User::where("id",$student)->first();

        $user_level_history = UserLevelHistory::where("new_level",$student->user_level)->first();
        if($user_level_history){
            $user_level_history->delete();
        }

        User::where("id",$student->id)->update(["user_level"=>$student->user_level-1]);

        return redirect()->route("students")->with(["message_info"=>"User level has been updated"]);

    }

    public function studentUpLevel(Request $request){
        $student = $request->get("student_id");
        $student = User::where("id",$student)->first();
        UserLevelHistory::create(["user_id"=>$student->id,"last_level"=>$student->user_level,"new_level"=>$student->user_level+1]);
        User::where("id",$student->id)->update(["user_level"=>$student->user_level+1]);

        return redirect()->route("students")->with(["message_info"=>"User level has been updated"]);
    }

    public function getStudents(){
        $user = User::getCurrent();
        $students=[];
        $classes = Classes::where("teacher_id",$user->id)->orderBy("class_time","desc")->get();


        foreach ($classes as $class){
            if($class->student){
                $students[$class->student->id]=$class->student;
            }
        }


        //Role::where('name','student')->first()->users()->get();
        return view("students.students",["students"=>$students]);
    }

    public function getStudentsProgress($user_id){
        $user=User::where("id",$user_id)->first();
        if(!$user){
            return redirect()->route("login")->with(["message_info"=>"Your session has expired"]);
        }

        $subscription=Subscription::where("user_id",$user->id)->where("status","active")->first();

        if(!$subscription){
            $subscription=Subscription::where("user_id",$user->id)->first();
        }

        if(!$subscription){
            $subscriptionType="real";
        } else {
            if (in_array($subscription->plan['name'], ["baselang_dele", "baselang_dele_trial", "medellin_DELE"])) {
                $subscriptionType = "dele";
            } elseif (in_array($subscription->plan['name'], ["baselang_dele_realworld", "baselang_dele_realworld_trial"])) {
                $subscriptionType = "real";
            } elseif (in_array($subscription->plan['name'], ["baselang_99", "baselang_99_trial", "baselang_129", "baselang_129_trial", "9zhg", "baselang_hourly", "medellin_RW", "baselang_149", "baselang_149_trial, medellin_RW_Lite, medellin_RW_1199"])) {
                $subscriptionType = "real";
            }
        }

        $levels_summary=new \stdClass();
        $statistics=new \stdClass();

        if(!isset($subscriptionType)){
            $subscriptionType="real";
        }

        if($subscriptionType=="real"){
            $progressLevel=$user->getProgressLevels($subscriptionType);
            $levels_summary->completed=$progressLevel[1];
            $levels_summary->total=$progressLevel[0];
            $levels=$progressLevel[2];
        } else {
            $levels=new \stdClass();
            $levels_summary->grammar=new \stdClass();
            $levels_summary->skills=new \stdClass();
            $levels_summary->test=new \stdClass();

            $progressLevel=$user->getProgressLevels("grammar");
            $levels_summary->grammar->completed=$progressLevel[1];
            $levels_summary->grammar->total=$progressLevel[0];
            $levels->grammar=$progressLevel[2];

            $progressLevel=$user->getProgressLevels("skills");
            $levels_summary->skills->completed=$progressLevel[1];
            $levels_summary->skills->total=$progressLevel[0];
            $levels->skills=$progressLevel[2];

            $progressLevel=$user->getProgressLevels("test");
            $levels_summary->test->completed=$progressLevel[1];
            $levels_summary->test->total=$progressLevel[0];
            $levels->test=$progressLevel[2];
        }

        $progressLevel=$user->getProgressLevels("elective");
        $levels_summary->completed_elective=$progressLevel[1];
        $levels_summary->total_elective=$progressLevel[0];
        $electives=$progressLevel[2];

        $user_classes=Classes::where("user_id",$user->id)->where("class_time","<=",gmdate("Y-m-d H:i:s"))->where("type",$subscriptionType)->get();
        $statistics->total_classes=$user_classes->count();
        $statistics->user_classes_month=Classes::where("user_id",$user->id)->where("class_time",">=",gmdate("Y-m-")."01 00:00:00")->where("class_time","<=",gmdate("Y-m-d H:i:s"))->where("type",$subscriptionType)->count();
        $statistics->user_classes_week=Classes::where("user_id",$user->id)->where("class_time",">=",\DateTime::createFromFormat("U",strtotime('monday this week'))->format("Y-m-d")." 00:00:00")->where("class_time","<=",gmdate("Y-m-d H:i:s"))->where("type",$subscriptionType)->count();
        $statistics->user_level_month=$user->levelProgressInTeacher($subscriptionType);

        $teachers=[];
        foreach($user_classes->groupBy("teacher_id") as $k=>$teacher){
            $teachers[$k]=$teacher->count();
        };
        arsort($teachers);
        $teachers=array_slice($teachers,0,6,true);
        foreach($teachers as $k=>&$teacher){
            $total=$teacher;
            $teacher=User::where("id",$k)->first();
            $teacher->total_classes=$total;
        };

        return view("students.progress",["student"=>$user,"level_progress"=>$user->getProgressInTeacher($subscriptionType),"menu_active"=>"progress","levels"=>$levels,"electives"=>$electives,"levels_summary"=>$levels_summary,"statistics"=>$statistics,"teachers"=>$teachers,"subscriptionType"=>$subscriptionType,"menu_active"=>"students"]);
    }

}
