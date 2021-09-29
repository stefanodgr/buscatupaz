<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Level;
use App\Models\Location;
use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;

class ClassesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "classes");
    }

    public function getTableIndex($from=false,$till=false,$filter_teacher=0,$filter_student=0){
        if(!$from){
            $from=gmdate("Y-m-d");
        }

        if(!$till){
            $till=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d");
        }

        $teachers = Role::where("name","teacher")->first()->users()->orderBy("first_name")->get();
        $students = Role::where("name","student")->first()->users()->orderBy("first_name")->get();

        return view("admin.classes.table",["teachers"=>$teachers,"from"=>$from,"till"=>$till,"filter_teacher"=>$filter_teacher,"students"=>$students,"filter_student"=>$filter_student]);
    }

    public function getIndex($from=false,$till=false,$filter_teacher=0,$filter_student=0){

        if(!$from){
            $from=gmdate("Y-m-d");
        }

        if(!$till){
            $till=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d");
        }

        $teachers = Role::where("name","teacher")->first()->users()->orderBy("first_name")->get();
        $students = Role::where("name","student")->first()->users()->orderBy("first_name")->get();
        return view("admin.classes.list",["teachers"=>$teachers,"from"=>$from,"till"=>$till,"filter_teacher"=>$filter_teacher,"students"=>$students,"filter_student"=>$filter_student]);
    }

    public function getTableFilter(Request $request){
        $from=$request->get("from");
        $till=$request->get("till");
        $teacher=$request->get("teacher");
        $student=$request->get("student");

        return redirect()->route("admin_classes_filtered_table",["from"=>$from,"till"=>$till,"teacher"=>$teacher,"student"=>$student]);
    }

    public function getFilter(Request $request){
        $from=$request->get("from");
        $till=$request->get("till");
        $teacher=$request->get("teacher");
        $student=$request->get("student");

        return redirect()->route("admin_classes_filtered",["from"=>$from,"till"=>$till,"teacher"=>$teacher,"student"=>$student]);
    }

    public function getList($from,$till,$teacher=0,$student=0){

        $classes = Classes::where("class_time",">=",$from." 00:00:00")->where("class_time","<=",$till." 23:59:59")->get();

        if($teacher){
            $classes = $classes->where("teacher_id",$teacher);
        }

        if($student){
            $classes = $classes->where("user_id",$student);
        }


        $classes_list=[];
        foreach($classes as $class){
            
            $location = null;
            if($class->location_id) {
                $location = Location::find($class->location_id);
                if($location) {
                    $location = ucwords(strtolower($location->name));
                }else {
                    $location = "Undefined";
                }
            }else {
                $location = "Online";
            }

            $classes_list[]=[$class->student->email." <span>".$class->student->first_name." ".$class->student->last_name."</span>",$class->teacher->first_name." ".$class->teacher->last_names,$class->type=="real"?"Real World":"DELE",\DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($class->teacher->timezone))->format("Y-m-d H:i:s"),$location,""];
        }

        return response()->json(['data' => $classes_list]);
    }

    public function getTable($from,$till,$teacher=0,$student=0){
        $classes = Classes::where("class_time",">=",$from." 00:00:00")->where("class_time","<=",$till." 23:59:59")->get();

        if($teacher){
            $classes = $classes->where("teacher_id",$teacher);
        }

        if($student){
            $classes = $classes->where("user_id",$student);
        }


        $classes_list=[];
        foreach($classes as $class){

            $classObj=new \stdClass();

            $DateTime=\DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($class->teacher->timezone));
            $classObj->student=$class->student->first_name." ".$class->student->last_name." ".$class->student->email;
            $classObj->teacher=$class->teacher->first_name." ".$class->teacher->last_name;
            $classObj->type=$class->type=="real"?"Real World":"DELE";
            $classObj->time=$DateTime->format("H:i");
            $classObj->day=$DateTime->format("D");

            $location = null;
            if($class->location_id) {
                $location = Location::find($class->location_id);
                if($location) {
                    $location = ucwords(strtolower($location->name));
                }else {
                    $location = "Undefined";
                }
            }else {
                $location = "Online";
            }

            $classObj->location=$location;

            $classes_list[]=$classObj;
        }

        return response()->json(['data' => $classes_list]);
    }

}
