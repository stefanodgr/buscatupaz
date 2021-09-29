<?php

namespace App\Http\Controllers\Admin;

use App\Models\Classes;
use App\Models\BlockDay;
use App\Models\BlockDayLogs;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use DB;

class BlockDayController extends Controller
{
    public function __construct() {
        View::share('menu_active','block_day');
    }

    public function getIndex() {
        return view("admin.block_day.list",["breadcrumb"=>true]);
    }

    public function getList() {
        $block_days=BlockDay::all();

        $block_days_list=[];
        foreach($block_days as $block_day){
            $block_days_list[]=[$block_day->teacher==null?"Teacher deleted":$block_day->teacher->email."<span>".$block_day->teacher->first_name." ".$block_day->teacher->last_name."</span>",\DateTime::createFromFormat("Y-m-d",$block_day->blocking_day)->format("Y/m/d"),$block_day->from==null?"-":$block_day->from,$block_day->till==null?"-":$block_day->till,'<a href="'.route("admin_block_day_edit",["block_day_id"=>$block_day->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a> <a href="'.route("admin_block_day_trash",["block_day_id"=>$block_day->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a>','<input type="checkbox" class="checkbox student_checkbox" name="student_checkbox[]" value="'.$block_day->id.'" />'];
        }

        return response()->json(['data'=>$block_days_list]);
    }

    function deleteAll(Request $request)
    {
        $current_user = User::getCurrent();
        $block_user_id_array = $request->input('id');

        $data = BlockDay::whereIn('id', $block_user_id_array)->get();
        $old_data = "";

        foreach ($data as $block_day){
            if($block_day->from && $block_day->till){
                $hours = $block_day->from.'-'.$block_day->till;
            } else {
                $hours = "Whole Day";
            }

            $old_data = $old_data.'Teacher:: '.$block_day->teacher->first_name.' '.$block_day->teacher->last_name.' ('.$block_day->teacher->email.') '.'<br>'. 'Date:: '.$block_day->blocking_day.'<br>'.'Hours:: '.$hours.'<br><br>';
        }

        $data_logs = array(
             "admin_id" => $current_user->id,
             "action" => 'Delete',
             "old_data" => $old_data,
             "new_data" => '-'
        );

        $block_day_logs=BlockDayLogs::create($data_logs);

        BlockDay::whereIn('id', $block_user_id_array)->delete();

        return redirect()->route("admin_block_day")->with(["message_info"=>"Blocked Days deleted"]);
    }

    public function getBlockDayLogs() {

        $block_days_logs = DB::table('block_days_logs')
       ->join('users', 'users.id', '=', 'block_days_logs.admin_id')
       ->select('users.email', 'block_days_logs.id', 'block_days_logs.admin_id', 'block_days_logs.action', 'block_days_logs.old_data', 'block_days_logs.new_data','block_days_logs.created_at')
       ->orderBy('block_days_logs.id', 'DESC')
       ->get();

        return view("admin.block_day.audit_log",["block_days_logs" =>$block_days_logs ,"breadcrumb"=>true]);
    }

    public function getCreate() {
    	$teachers=Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();

        return view("admin.block_day.create_for_teacher",["teachers"=>$teachers,"breadcrumb"=>true]);
    }

    public function create(Request $request) {
        $current_user = User::getCurrent();
        $data=$request->only(["teacher_id","blocking_day","from","till"]);

         $teacher_info=User::where("id",$data['teacher_id'])->first();
         $teacher_email = $teacher_info['email'];
         $teacher_first_name = $teacher_info['first_name'];
         $teacher_last_name = $teacher_info['last_name'];
         if($data['from'] && $data['till']){
             $hours = $data['from'].'-'.$data['till'];
         } else {
             $hours = "Whole Day";
         }
         if($data["teacher_id"]=="all")
         {
          $new_data = 'All_teachers <br> Date:: '.$data['blocking_day'].'<br> Hours:: '.$hours;
         }
         else
         {
          $new_data = 'Teacher:: '.$teacher_info['first_name'].' '.$teacher_info['last_name'].' ('.$teacher_info['email'].') '.'<br>'. 'Date:: '.$data['blocking_day'].'<br>'.'Hours:: '.$hours;
         }
         $data_logs = array(
             "admin_id" => $current_user->id,
             "action" => 'Create',
             "old_data" => '-',
             "new_data" => $new_data
         );

        if(empty($data["blocking_day"])) {
        	return redirect()->back()->withErrors(["You must select a blocking day!"]);
        }

        if($data["teacher_id"]=="all") {
            $block_day_logs=BlockDayLogs::create($data_logs);
            $teachers=Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();
            foreach($teachers as $teacher) {
                $data["teacher_id"]=$teacher->id;
                $data["public_holiday"]= 1;
                $block_day=BlockDay::create($data);
                \Log::info("New blocking day - Teacher: ".$block_day->teacher->email." - Blocking day: ".$block_day->blocking_day." - From: ".$block_day->from." - Till: ".$block_day->till." - Current user: ".$current_user->email);
            }
            return redirect()->route("admin_block_day")->with(["message_info"=>"Blocked Day created"]);
        }else {
            $block_day=BlockDay::create($data);
            $data_logs["block_day_id"] = DB::getPdo()->lastInsertId();
            $block_day_logs=BlockDayLogs::create($data_logs);
            \Log::info("New blocking day - Teacher: ".$block_day->teacher->email." - Blocking day: ".$block_day->blocking_day." - From: ".$block_day->from." - Till: ".$block_day->till." - Current user: ".$current_user->email);
            return redirect()->route("admin_block_day_edit", ["block_day_id" => $block_day->id])->with(["message_info"=>"Blocked Day created"]);
        }
    }

    public function getEdit($block_day_id) {
        $block_day=BlockDay::find($block_day_id);

        if(!$block_day){
            return redirect()->route("admin_block_day")->with(["message_info"=>"Blocked Day ".$block_day_id." does not exist"]);
        }

        $teachers=Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();

        return view("admin.block_day.edit_for_teacher",["teachers"=>$teachers,"breadcrumb"=>true,"block_day"=>$block_day]);
    }

    public function update(Request $request) {
        $current_user = User::getCurrent();
        $data=$request->only(["teacher_id","blocking_day","from","till"]);
        $block_day_id=$request->get("block_day_id");

        $block_day=BlockDay::find($block_day_id);
        \Log::info("Edit blocking day - Teacher: ".$block_day->teacher->email." - Blocking day: ".$block_day->blocking_day." - From: ".$block_day->from." - Till: ".$block_day->till." - Current user: ".$current_user->email);

        BlockDay::where("id",$block_day_id)->update($data);

         $teacher_info=User::where("id",$data['teacher_id'])->first();
         $teacher_email = $teacher_info['email'];
         $teacher_first_name = $teacher_info['first_name'];
         $teacher_last_name = $teacher_info['last_name'];
 
         if($data['from'] && $data['till']){
             $hours = $data['from'].'-'.$data['till'];
         } else {
             $hours = "Whole Day";
         }
 
         $old_data = "";
         $auditTblData=BlockDayLogs::where("block_day_id",$block_day_id)->orderBy('id', 'desc')->first();
         if($auditTblData)
         {
           $old_data = $auditTblData->new_data;
         }
         $new_data = 'Teacher:: '.$teacher_info['first_name'].' '.$teacher_info['last_name'].' ('.$teacher_info['email'].') '.'<br>'. 'Date:: '.$data['blocking_day'].'<br>'.'Hours:: '.$hours;
         
         $data_logs = array(
             "admin_id" => $current_user->id,
             "block_day_id" => $block_day_id,
             "action" => 'Update',
             "old_data" => $old_data,
             "new_data" => $new_data
         );
         $block_day_logs=BlockDayLogs::create($data_logs);

        $block_day=BlockDay::find($block_day_id);
        \Log::info("Update blocking day - Teacher: ".$block_day->teacher->email." - Blocking day: ".$block_day->blocking_day." - From: ".$block_day->from." - Till: ".$block_day->till." - Current user: ".$current_user->email);

        return redirect()->route("admin_block_day_edit",["block_day_id"=>$block_day_id])->with(["message_info"=>"Blocked Day updated"]);
    }

    public function getTrash($block_day_id) {
        $block_day=BlockDay::find($block_day_id);

        if(!$block_day){
            return redirect()->route("admin_block_day")->with(["message_info"=>"Blocked Day ".$block_day_id." does not exist"]);
        }

        return view("admin.block_day.trash",["breadcrumb"=>true,"block_day"=>$block_day]);
    }

    public function delete(Request $request) {
        $current_user = User::getCurrent();
        $block_day_id=$request->get("block_day_id");

        $block_day=BlockDay::find($block_day_id);

        if($block_day->from && $block_day->till){
            $hours = $block_day->from.'-'.$block_day->till;
        } else {
            $hours = "Whole Day";
        }

        $old_data = 'Teacher:: '.$block_day->teacher->first_name.' '.$block_day->teacher->last_name.' ('.$block_day->teacher->email.') '.'<br>'. 'Date:: '.$block_day->blocking_day.'<br>'.'Hours:: '.$hours;
        $data_logs = array(
             "admin_id" => $current_user->id,
             "block_day_id" => $block_day_id,
             "action" => 'Delete',
             "old_data" => $old_data,
             "new_data" => '-'
         );
        $block_day_logs=BlockDayLogs::create($data_logs);
         
        \Log::info("Delete blocking day - Teacher: ".$block_day->teacher->email." - Blocking day: ".$block_day->blocking_day." - From: ".$block_day->from." - Till: ".$block_day->till." - Current user: ".$current_user->email);
        $block_day->delete();

        return redirect()->route("admin_block_day")->with(["message_info"=>"Blocked Day deleted"]);
    }
}
