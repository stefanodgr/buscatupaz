<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Level;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\UserCancellation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use DB;

class CancellationsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "cancellations");
    }

    public function getTableIndex($from=false,$till=false,$filter_teacher=0,$filter_student=0){
        if(!$from){
            $from=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P7D"))->format("Y-m-d");
        }

        if(!$till){
            $till=gmdate("Y-m-d");
        }

        $teachers = Role::where("name","teacher")->first()->users()->get();
        $students = Role::where("name","student")->first()->users()->orderBy("first_name")->get();

        return view("admin.cancellations.table",["teachers"=>$teachers,"from"=>$from,"till"=>$till,"filter_teacher"=>$filter_teacher,"students"=>$students,"filter_student"=>$filter_student]);
    }

    public function getIndex($from=false,$till=false){

        if(!$from){
            $from=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P7D"))->format("Y-m-d");
        }

        if(!$till){
            $till=gmdate("Y-m-d");
        }

        $students = Role::where("name","student")->first()->users()->orderBy("first_name")->get();
        return view("admin.cancellations.list",["from"=>$from,"till"=>$till,"students"=>$students]);
    }

    public function getTableFilter(Request $request){
        $from=$request->get("from");
        $till=$request->get("till");
        $teacher=$request->get("teacher");
        $student=$request->get("student");

        return redirect()->route("admin_cancellations_filtered_table",["from"=>$from,"till"=>$till,"teacher"=>$teacher,"student"=>$student]);
    }

    public function getFilter(Request $request){
        $from=$request->get("from");
        $till=$request->get("till");

        return redirect()->route("admin_cancellations_filtered",["from"=>$from,"till"=>$till]);
    }

    public function getList($from,$till){
        $cancellations = UserCancellation::where("created_at",">=",$from." 00:00:00")->where("created_at","<=",$till." 23:59:59")->get();
        $reason = DB::table('cancellation_reasons')->get();
        $key = [];
        $value = [];
        foreach($reason as $row){
            $key[] =  $row->id;
            $value[] =  $row->option;
        }
        $reasons=array_combine($key,$value);
        $cancellations_list=[];

        foreach($cancellations as $cancellation){

            if($cancellation->user)
            {
                if(!isset($reasons[$cancellation->reason_id])){
                    $reason="N/A";
                }
                else{
                    $reason=$reasons[$cancellation->reason_id];
                }

                $cancellations_list[]=[$cancellation->user->email." <span>".$cancellation->user->first_name." ".$cancellation->user->last_name."</span>",$reason,substr($cancellation->other,0,280),$cancellation->created_at->format('Y-m-d H:i:s')];
            }
        }
        
        return response()->json(['data' => $cancellations_list]);
    }

    public function csvSummary()
    {
        $cancellations = UserCancellation::get();
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($cancellations, ['user.last_name','user.first_name','user.email','reason','other','created_at'])->download();
    }

    public function getTable($from,$till){
        $cancellations = UserCancellation::where("created_at",">=",$from." 00:00:00")->where("created_at","<=",$till." 23:59:59")->get();
        $reason = DB::table('cancellation_reasons')->get();
        $key = [];
        $value = [];
        foreach($reason as $row){
            $key[] =  $row->slug;
            $value[] =  $row->option;
        }
        $reasons=array_combine($key,$value);
        $cancellations_list=[];

        foreach($cancellations as $cancellation){

            $classObj=new \stdClass();

            if(!isset($reasons[$cancellation->reason])){
                $reason="N/A";
            }
            else{
                $reason=$reasons[$cancellation->reason];
            }
            // echo "<pre>";
            // print_r($cancellation->user);
            // exit;
            $DateTime=\DateTime::createFromFormat("Y-m-d H:i:s",$cancellation->created_at->format("Y-m-d H:i:s"));
            $classObj->student=$cancellation->user['first_name']." ".$cancellation->user['last_name']." ".$cancellation->user['email'];
            $classObj->type=$reason;
            $classObj->time=$DateTime->format("H:i");
            $classObj->day=$DateTime->format("D");
            $cancellations_list[]=$classObj;
        }

        return response()->json(['data' => $cancellations_list]);
    }

}
