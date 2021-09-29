<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Location;
use App\Models\Level;
use App\Models\Role;
use App\Models\Statistics;
use App\Models\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "dashboard");
    }



    public function getAdminDashboard(){
        
        $students = Role::where('name','student')->first()->users()->where("activated",1)->get();
        
        $online_active = 0;
        $online_rw = 0;
        $online_rw_free_days = 0;
        $online_rw_paused = 0;
        $online_dele = 0;
        $online_dele_free_days = 0;
        $online_dele_paused = 0;
        $online_hourly = 0;
        $online_hourly_free_days = 0;
        $online_hourly_paused = 0;
        $medellin_active = 0;
        $medellin_rw_mo = 0;
        $medellin_rw_wk = 0;
        $medellin_rw_1199_mo = 0;
        $medellin_rw_lite_mo = 0;
        $medellin_rw_free_days = 0;
        $medellin_rw_paused = 0;
        $medellin_rw_start_soon = 0;
        $medellin_dele_mo = 0;
        $medellin_dele_wk = 0;
        $medellin_dele_free_days = 0;
        $medellin_dele_paused = 0;
        $medellin_dele_start_soon = 0;
        $medellin_sm_active = 0;
        $medellin_sm_start_soon = 0;
        
        return view("main.admin_dashboard",["menu_active"=>"dashboard", "online_active"=>$online_active, "online_rw"=>$online_rw, "online_rw_free_days"=>$online_rw_free_days, "online_rw_paused"=>$online_rw_paused, "online_dele"=>$online_dele, "online_dele_free_days"=>$online_dele_free_days, "online_dele_paused"=>$online_dele_paused, "online_hourly"=>$online_hourly, "online_hourly_free_days"=>$online_hourly_free_days, "online_hourly_paused"=>$online_hourly_paused, "medellin_active"=>$medellin_active, "medellin_rw_mo"=>$medellin_rw_mo, "medellin_rw_wk"=>$medellin_rw_wk, "medellin_rw_free_days"=>$medellin_rw_free_days, "medellin_rw_paused"=>$medellin_rw_paused, "medellin_rw_start_soon"=>$medellin_rw_start_soon, "medellin_dele_mo"=>$medellin_dele_mo, "medellin_dele_wk"=>$medellin_dele_wk, "medellin_dele_free_days"=>$medellin_dele_free_days, "medellin_dele_paused"=>$medellin_dele_paused, "medellin_dele_start_soon"=>$medellin_dele_start_soon, "medellin_sm_active"=>$medellin_sm_active, "medellin_sm_start_soon"=>$medellin_sm_start_soon]);
    }

    public function getTeachersFavs($location_id){
        
        $students = Role::where('name','student')->first()->users()->where("activated",1)->where("favorite_teacher","<>",0)->get();

        //Favorites teacher
        $teachers=[];

        foreach($students as $student){

            $teacher=null;

            if($location_id=="all") {
                $teacher=User::where("id",$student->favorite_teacher)->first();
            }elseif($location_id=="no_location") {
                $teacher=User::where("id",$student->favorite_teacher)->where("location_id",null)->first();
            }else {
                $teacher=User::where("id",$student->favorite_teacher)->where("location_id",$location_id)->first();
            }

            if($teacher){
                if(!isset($teachers[$teacher->first_name])){
                    $teachers[$teacher->first_name]=0;
                }
                $teachers[$teacher->first_name]++;  
            }
        }

        ksort($teachers);

        return view("main.includes.teachers_favorites",["teachers"=>$teachers]);
    }

    public function getAdmin(){
        return redirect()->route("change_rol",["rol_name"=>"admin"]);
    }

    public function getLogReader()
    {
        return view("admin.log_reader.show",["menu_active"=>"log_reader"]);
    }

    public function getLogReaderDate($date)
    {
        $text="";
        $path="../storage/logs/laravel-".$date.".log";
        if(file_exists($path)){
            $fp = fopen($path, "r");
            while(!feof($fp)) {
                $line = fgets($fp);
                $text.=$line."\n";
            }
            fclose($fp);
            return response()->json(["text" => $text]);
        }
        else{
            return response()->json(["text" => 0]);
        }
    }

    public function getFeedback()
    {
        return view("admin.feedback.list",["breadcrumb"=>true, "menu_active"=>"feedback"]);
    }

    public function getListFeedback()
    {
        $feedbacks=Feedback::orderBy("created_at","desc")->get();

        $feedback_list=[];

        foreach($feedbacks as $feedback){
            $user=$feedback->user;
            if($user){
                $feedback_list[]=[$user->email." <span>".$user->first_name." ".$user->last_name."</span>", substr($feedback->feedback,0,280), $feedback->created_at->format('Y-m-d H:i:s')];
            }
        }

        return response()->json(['data' => $feedback_list]);    
    }

    public function csvFeedback()
    {
        $feedbacks=Feedback::orderBy("created_at","desc")->get();

        foreach($feedbacks as $feedback){
            $user=$feedback->user;
            if($user){
                $feedback->first_name=$user->first_name;
                $feedback->last_name=$user->last_name;
                $feedback->email=$user->email;
            }
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($feedbacks, ['first_name', 'last_name', 'email', 'feedback', 'created_at'])->download();
    }

    public function csvTeachersFavorites()
    {
        $students = Role::where('name','student')->first()->users()->where("activated",1)->where("favorite_teacher","<>",0)->get();
        //Favorites teacher
        $teachers=[];
        foreach($students as $student){
            $teacher=User::where("id",$student->favorite_teacher)->first();
            if($teacher){
                if(!isset($teachers[strtolower($teacher->email)])){
                    $teachers[strtolower($teacher->email)]=0;
                }
                $teachers[strtolower($teacher->email)]++;
            }
        }
        ksort($teachers);
        $new_teachers = new \Illuminate\Database\Eloquent\Collection;
        foreach($teachers as $key => $teacher) {
            $teach = User::where("email",$key)->first();
            if($teach) {
                $new_teacher = new User();
                $new_teacher->first_name = $teach->first_name;
                $new_teacher->last_name = $teach->last_name;
                $new_teacher->email = $teach->email;
                $new_teacher->score = $teacher;

                $location = null;
                if($teach->location_id) {
                    $location = Location::find($teach->location_id);
                    if($location) {
                        $location = ucwords(strtolower($location->name));
                    }else {
                        $location = "Undefined";
                    }
                }else {
                    $location = "Online";
                }

                $new_teacher->location = $location;
                $new_teachers->push($new_teacher);
            }
        }
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($new_teachers, ['first_name','last_name','email','score','location'])->download();
    }

    public function csvHistoryTeachersFavorites()
    {
        $statistics=Statistics::where("type","Favorite_teacher")->get();
        $teachers=[];
        foreach($statistics as $statistic) {
            $teacher = User::where("id",$statistic->data_x)->first();
            if($teacher){
                if(!isset($teachers[strtolower($teacher->email)])){
                    $teachers[strtolower($teacher->email)]=0;
                }
                $teachers[strtolower($teacher->email)]++;
            }
        }
        ksort($teachers);
        $new_teachers = new \Illuminate\Database\Eloquent\Collection;
        foreach($teachers as $key => $teacher) {
            $teach = User::where("email",$key)->first();
            if($teach) {
                $new_teacher = new User();
                $new_teacher->first_name = $teach->first_name;
                $new_teacher->last_name = $teach->last_name;
                $new_teacher->email = $teach->email;
                $new_teacher->score = $teacher;

                $location = null;
                if($teach->location_id) {
                    $location = Location::find($teach->location_id);
                    if($location) {
                        $location = ucwords(strtolower($location->name));
                    }else {
                        $location = "Undefined";
                    }
                }else {
                    $location = "Online";
                }

                $new_teacher->location = $location;
                $new_teachers->push($new_teacher);
            }
        }
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($new_teachers, ['first_name','last_name','email','score','location'])->download();
    }

    public function csvSubscriptionsStatus()
    {
        $students = Role::where('name','student')->first()->users()->where("activated",1)->orderBy("first_name","ASC")->get();
        
        foreach($students as $student){
            if($student->getCurrentSubscription()) {
                $student->active = "Yes";
            }else {
                $student->active = "No";
            }
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($students, ['first_name','last_name','email','active'])->download();
    }
}
