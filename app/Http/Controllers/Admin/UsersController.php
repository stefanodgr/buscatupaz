<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\ActiveDeleTrial;
use App\Models\BuyPrebook;
use App\Models\Classes;
use App\Models\DeleTrialTest;
use App\Models\Interests;
use App\Models\UserFreeDays;
use App\Models\Level;
use App\Models\Location;
use App\Models\LogAdmin;
use App\Models\Prebook;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\UserCalendar;
use App\Models\UserCancellation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "users");
    }

    public function getIndex(){
        return view("admin.users.list",["menu_active"=>"users","breadcrumb"=>true]);
    }

    public function updateSubscription($user_id){
        $user=User::where("id",$user_id)->first();
        $user->updateSubscriptionInfo();

        return redirect()->route("admin_users_edit",["user_id"=>$user_id])->with(["message_info"=>"User subscription has been updated"]);
    }

    public function cancelSubscription($user_id){
        $user=User::where("id",$user_id)->first();
        $reason='Cancelled by Admin';

        $user_subscription=$user->getCurrentSubscription();
        if($user_subscription)
        {
            try {
                \ChargeBee_Subscription::cancel($user_subscription->subscription_id,[
                "endOfTerm" => $user_subscription->plan_name!='hourly' && $user_subscription->status!='in_trial'
            ]);

                $user->last_plan=$user_subscription->plan_name;
                $user->secureSave();
                UserCancellation::create(["user_id"=>$user->id,"reason"=>$reason]);
                $user->refreshInformation();
            } catch (\Exception $e){
                Error::reportError('Error canceling subscription',$e->getLine(),$e->getMessage());
            }
        }

        return $this->updateSubscription($user_id);
    }

    public function cancelSubscriptionImmediately($user_id){
        $user=User::where("id",$user_id)->first();
        $reason='Cancelled by Admin';

        $user_subscription=$user->getCurrentSubscription();
        if($user_subscription)
        {
            try {
                \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                $user->last_plan=$user_subscription->plan_name;
                $user->secureSave();
                UserCancellation::create(["user_id"=>$user->id,"reason"=>$reason]);
                $user->refreshInformation();
            } catch (\Exception $e){
                Error::reportError('Error canceling subscription',$e->getLine(),$e->getMessage());
            }
        }

        return $this->updateSubscription($user_id);
    }

    public function addElective(Request $request){
        $user = User::getCurrent();
        $elective_id=$request->get("elective");
        $user_id=$request->get("user_id");

        $edit_user=User::where("id",$user_id)->first();
        $user_elective=$edit_user->getElectives()->where("id",$elective_id)->first();

        if(!$user_elective){
            $elective=Level::where("id",$elective_id)->where("type","elective")->first();
            if($elective){
                $user_level=$edit_user->levels()->where("level_id",$elective->id)->first();
                if(!$user_level){
                    $edit_user->levels()->save($elective);
                }

                $edit_user->levels()->updateExistingPivot($elective->id,["paid"=>1,"transaction_id"=>("admin".$user->id)]);
            }
        }

        return redirect()->route("admin_users_edit",["user_id"=>$user_id])->with(["message_info"=>"User elective has been added"]);

    }
    public function addFreeDays(Request $request){
        $current_user = User::getCurrent();

        $user_id=$request->get("user_id");
        $days=$request->get("days");

        $user=User::where("id",$user_id)->first();
        $user->addFreeDays($days);
        $user->refreshInformation();

        UserFreeDays::create(["user_id"=>$user_id,"referred_id"=>$current_user->id,"active"=>1,"claimed"=>1,"available"=>1,"free_days"=>$days,"admin"=>1]);
        
        return redirect()->route("admin_users_edit",["user_id"=>$user_id])->with(["message_info"=>"User subscription has been updated"]);
    }

    public function getList(){
        $users = User::get();

        $all_roles = array();
        $role_names = DB::table("roles")->get();
        foreach($role_names as $role_name){
            $all_roles[$role_name->id] = $role_name->name;
        }

        $user_roles = DB::table("role_user")->get();

        $all_users_roles = array();
        foreach($user_roles as $user_role){
            if(array_key_exists($user_role->user_id, $all_users_roles)){
                $user_role_array = $all_users_roles[$user_role->user_id]; 
                array_push($user_role_array, $all_roles[$user_role->role_id]);
                $all_users_roles[$user_role->user_id] = $user_role_array;
            } else{
                $user_role_array = array();
                array_push($user_role_array, $all_roles[$user_role->role_id]);
                $all_users_roles[$user_role->user_id] = $user_role_array;
            }
        }

        $user_list=[];
        foreach($users as $user){
            $roles = "";
            $u_roles = false; 
            if(array_key_exists($user->id, $all_users_roles)){
                $u_roles = $all_users_roles[$user->id];
            }
            $admin_role = false;
            if($u_roles) {
                foreach($u_roles as $k=>$u_role){
                    if(!$admin_role && $u_role=="admin"){
                        $admin_role = true;
                    }
                    if($u_role=="student" && $user->last_unlimited_subscription){
                        $roles.="<span>".($u_role)." (".$user->last_unlimited_subscription.")</span>";
                    } else {
                        $roles.="<span>".($u_role)."</span>";
                    }

                    if($k+1 != sizeof($u_roles)){
                        $roles.=" ";
                    }

                }    
            }
            if($roles==""){
                $roles="<span>student: (baselang_149)</span>";
            }

            if($admin_role){
                $user_list[]=[$user->email." <span>".$user->first_name." ".$user->last_name."</span>",$roles,$user->created_at->format("Y/m/d"),$user->activated=="1"?"Active":"Inactive",'<a href="'.route("admin_users_edit",["user_id"=>$user->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_users_trash",["user_id"=>$user->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a><i class="fa fa-eye" aria-hidden="true"></i>'];
            } else{
                $user_list[]=[$user->email." <span>".$user->first_name." ".$user->last_name."</span>",$roles,$user->created_at->format("Y/m/d"),$user->activated=="1"?"Active":"Inactive",'<a href="'.route("admin_users_edit",["user_id"=>$user->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_users_trash",["user_id"=>$user->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a><a href="'.route("user_impersonate",["user_id"=>$user->id]).'"><i class="fa fa-eye" aria-hidden="true"></i></a>'];
            }
        }

        return response()->json(['data' => $user_list]);
    }

    public function getListFilter($type){
        $users = Role::where('name','student')->first()->users()->where("activated",1)->get();

        $user_list = [];
        foreach($users as $user){
            $roles = "";
            foreach($user->roles as $k=>$user_rol){

                if($user_rol->name=="student" && $user->last_unlimited_subscription){
                    $roles.="<span>".($user_rol->name)." (".$user->last_unlimited_subscription.")</span>";
                } else {
                    $roles.="<span>".($user_rol->name)."</span>";
                }

                if($k+1!=count($user->roles)){
                    $roles.=" ";
                }

            }

            if($roles==""){
                $roles="<span>student: (baselang_149)</span>";
            }

            $subscription = $user->subscriptions->sortBy("ends_at")->first();

            if($subscription && $subscription->status=="future" && $user->last_unlimited_subscription) {
                $subscription->plan->name = $user->last_unlimited_subscription;
            }

            $verify = false;

            if($type=="online_rw" && $subscription && in_array($subscription->plan->name,["baselang_99", "baselang_99_trial", "baselang_129", "baselang_129_trial", "baselang_149", "baselang_149_trial"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            if($type=="online_dele" && $subscription && in_array($subscription->plan->name,["baselang_dele", "baselang_dele_trial", "baselang_dele_test"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            if($type=="online_hourly" && $subscription && in_array($subscription->plan->name,["baselang_hourly"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            if($type=="medellin_rw_mo" && $subscription && in_array($subscription->plan->name,["medellin_RW"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            if($type=="medellin_rw_1199_mo" && $subscription && in_array($subscription->plan->name,["medellin_RW_1199"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            if($type=="medellin_rw_lite_mo" && $subscription && in_array($subscription->plan->name,["medellin_RW_Lite"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            if($type=="medellin_dele_mo" && $subscription && in_array($subscription->plan->name,["medellin_DELE"]) && ($subscription->status=="active" ||  $subscription->status=="future")) {
                $verify = true;
            }

            $paid_inmersion = $user->paid_inmersions->sortBy("inmersion_start")->first();

            if($type=="medellin_sm" && $paid_inmersion && gmdate("Y-m-d")>=$inmersion->inmersion_start){
                $verify = true;
            }

            if($verify){
                $user_list[]=[$user->email." <span>".$user->first_name." ".$user->last_name."</span>",$roles,$user->created_at->format("Y/m/d"),$user->activated=="1"?"Active":"Inactive",'<a href="'.route("admin_users_edit",["user_id"=>$user->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_users_trash",["user_id"=>$user->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a><a href="'.route("user_impersonate",["user_id"=>$user->id]).'"><i class="fa fa-eye" aria-hidden="true"></i></a>'];
            }

        }

        return response()->json(['data' => $user_list]);
    }

    public function impersonate($user_id){
        $user = User::getCurrent();
        session(["impersonated_by"=>$user->id]);
        session(['current_rol'=>null]);
        Auth::loginUsingId($user_id, true);

        return redirect()->route("dashboard");
    }

    public function backImpersonate()
    {
        $impersontate_by=session("impersonated_by");
        if($impersontate_by){
            Session::flush();
            Auth::loginUsingId($impersontate_by, true);
        }

        return redirect()->route("home");
    }

    public function delete(Request $request){
        $user=User::where("id",$request->get("user_id"))->first();
        $current_user=User::getCurrent();

        \Log::info("User: " .$user->email. " Deleted By: ".$current_user->email);
        Classes::where("teacher_id",$user->id)->orWhere("user_id",$user->id)->delete();
        User::where("id",$user->id)->delete();

        return redirect()->route("admin_users")->with(["message_info"=>"User deleted"]);
    }

    public function create(Request $request){
  
        $current_user=User::getCurrent();

        $data=($request->only(["activated","first_name","last_name","id_number","mobile_number","email","zoom_email","timezone","description","enterprise","city","location","is_deleteacher","chargebee_id"]));
        $data=array_filter($data);
        $data["password"]=Hash::make("12345");

        \Log::info("User: " .$data["email"]. " Created By: ".$current_user->email);

        $check_user=User::where("email",$data["email"])->first();

        if($check_user){
            return redirect()->back()->withErrors(["This email (".$data["email"].") is already registered"]);
        }

        if(!isset($data["activated"]) || !$data["activated"]){
            $data["activated"]=0;
        } else {
            $data["activated"]=1;
        }

        $user=User::create($data);

        if(!$user->activated){
            $user->activated=1;
            User::where("id",$user->id)->update(["activated"=>1]);
        }

        try {
            if(\App::environment('production')){
                \Mail::send('emails.user_welcome', ["user" => $user], function ($message) use ($user) {
                    $message->subject("Welcome to Baselang!");
                    $message->to($user->email, $user->first_name);
                });
            }
        } catch (\Exception $e) {
            Log::error('Cant send email: '.$e->getMessage());
        }

        $roles=$request->get("roles");

        $user->detachRoles($user->roles);

        if(!$roles){
            $user->attachRole(Role::where("name","student")->first());
        } else {
            foreach($roles as $rol){
                $user->attachRole(Role::where("name",$rol)->first());
            }
        }

        return redirect()->route("admin_users_edit",["user_id"=>$user->id]);
    }
    
    public function update(Request $request){

        $current_user=User::getCurrent();

        $data=($request->only(["activated","first_name","last_name","id_number","mobile_number","email","zoom_email","timezone","description","favorite_teacher","user_level","enterprise","city","location","youtube_url","credits","dele_sheet","real_sheet","electives_sheet","is_deleteacher","block_online","block_prebook","chargebee_id","location_id"]));

        $data=array_filter($data);

        if(!isset($data["chargebee_id"])){
            $data["chargebee_id"]="";
        }

        $roles=$request->get("roles");

        $user_id=$request->get("user_id");

        $edit_user=User::where("id",$user_id)->first();

        \Log::info("User: " .$edit_user->email. " Updated By: ".$current_user->email);
        if(isset($data["credits"])){
            \Log::info("Credits: " .$edit_user->credits. " Updated By: ".$data["credits"]);
        }
        
        $check_user=User::where("email",$data["email"])->first();
        if($check_user && $check_user->id!=$user_id){
            return redirect()->back()->withErrors(["Ese correo ya se encuentra registrado"]);
        }

        $password=$request->get("password");
        if($password && !empty($password)){
            $confirm_password=$request->get("confirm_password");

            if($password!=$confirm_password){
                return redirect()->back()->withErrors(["Passwords don't match."]);
            }

            if(strlen($password)<5){
                return redirect()->back()->withErrors(["Passwords must be at least 5 characters"]);
            }

            User::where("id",$edit_user->id)->update(["password"=>Hash::make($password)]);
        }

        if(!isset($data["is_deleteacher"]) || !$data["is_deleteacher"]){
            $data["is_deleteacher"]=0;
        } else {
            $data["is_deleteacher"]=1;
        }

        if(!isset($data["block_online"]) || !$data["block_online"]){
            $data["block_online"]=0;
        } else {
            $data["block_online"]=1;
        }

        if(!isset($data["block_prebook"]) || !$data["block_prebook"]){
            $data["block_prebook"]=0;
        } else {
            $data["block_prebook"]=1;
        }

        /*if(isset($data["location_id"]) && $data["location_id"]=="none"){
            $data["location_id"]=null;
        }*/

        if(!isset($data["activated"]) || !$data["activated"]){
            $data["activated"]=0;
            if($edit_user->hasRole("student")){
                $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$edit_user->id)->get();

                /*
                foreach($classes as $key => $class){
                    $class->removeZoom();
                    $class->delete();
                }
                */
            }
        } else {
            $data["activated"]=1;
        }

        $verify_last_roles=[];
        foreach ($edit_user->roles as $role) {
            $verify_last_roles[]=$role->display_name;
        }

        $edit_user->detachRoles($edit_user->roles);
        foreach($roles as $rol){
            $edit_user->attachRole(Role::where("name",$rol)->first());
        }

        if($request->file('change_profile_picture')) {
            Storage::disk("uploads")->putFileAs('/assets/users/photos',$request->file('change_profile_picture'),$edit_user->id.".jpg");
            $request->change_profile_picture->storeAs('assets/users/photos',$edit_user->id.'.jpg','uploads');
        }

        User::where("id",$user_id)->update($data);
        $updated_user=User::where("id",$user_id)->first();

        $verify_present_roles=[];
        foreach ($updated_user->roles as $role) {
            $verify_present_roles[]=$role->display_name;
        }

        if($updated_user->first_name != $edit_user->first_name) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"First Name", "old_data"=>$edit_user->first_name==null?"N/A":$edit_user->first_name, "new_data"=>$updated_user->first_name==null?"N/A":$updated_user->first_name]);
        }

        if($updated_user->last_name != $edit_user->last_name) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Last Name", "old_data"=>$edit_user->last_name==null?"N/A":$edit_user->last_name, "new_data"=>$updated_user->last_name==null?"N/A":$updated_user->last_name]);
        }

        if($updated_user->id_number != $edit_user->id_number) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"ID Number", "old_data"=>$edit_user->id_number==null?"N/A":$edit_user->id_number, "new_data"=>$updated_user->id_number==null?"N/A":$updated_user->id_number]);
        }

        if($updated_user->mobile_number != $edit_user->mobile_number) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Mobile Number", "old_data"=>$edit_user->mobile_number==null?"N/A":$edit_user->mobile_number, "new_data"=>$updated_user->mobile_number==null?"N/A":$updated_user->mobile_number]);
        }

        if($updated_user->email != $edit_user->email) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Email", "old_data"=>$edit_user->email==null?"N/A":$edit_user->email, "new_data"=>$updated_user->email==null?"N/A":$updated_user->email]);
        }

        if($updated_user->zoom_email != $edit_user->zoom_email) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Zoom Email", "old_data"=>$edit_user->zoom_email==null?"N/A":$edit_user->zoom_email, "new_data"=>$updated_user->zoom_email==null?"N/A":$updated_user->zoom_email]);
        }

        if($updated_user->timezone != $edit_user->timezone) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Timezone", "old_data"=>$edit_user->timezone==null?"N/A":$edit_user->timezone, "new_data"=>$updated_user->timezone==null?"N/A":$updated_user->timezone]);
        }

        if($updated_user->activated != $edit_user->activated) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Activate", "old_data"=>$edit_user->activated==1?"Active":"Inactive", "new_data"=>$updated_user->activated==1?"Active":"Inactive"]);
        }

        /*if($updated_user->location_id != $edit_user->location_id) {

            $last_location = null;
            if($edit_user->location_id) {
                $last_location = Location::find($edit_user->location_id);
                if($last_location) {
                    $last_location = ucwords(strtolower($last_location->name));
                }else {
                    $last_location = "N/A";
                }
            }else {
                $last_location = "N/A";
            }

            $current_location = null;
            if($updated_user->location_id) {
                $current_location = Location::find($updated_user->location_id);
                if($current_location) {
                    $current_location = ucwords(strtolower($current_location->name));
                }else {
                    $current_location = "N/A";
                }
            }else {
                $current_location = "N/A";
            }

            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Location", "old_data"=>$last_location, "new_data"=>$current_location]);
        }*/

        if($updated_user->chargebee_id != $edit_user->chargebee_id) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Chargebee ID", "old_data"=>$edit_user->chargebee_id==null?"N/A":$edit_user->chargebee_id, "new_data"=>$updated_user->chargebee_id==null?"N/A":$updated_user->chargebee_id]);
        }

        if($verify_last_roles != $verify_present_roles) {
            $last_roles=null;
            foreach($verify_last_roles as $role) {
                $last_roles.=$role.", ";
            }

            $present_roles=null;
            foreach($verify_present_roles as $role) {
                $present_roles.=$role.", ";
            }

            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Roles", "old_data"=>$last_roles==null?"N/A":$last_roles, "new_data"=>$present_roles==null?"N/A":$present_roles]);
        }

        if($updated_user->password != $edit_user->password) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Password", "old_data"=>"not available", "new_data"=>$password==null?"N/A":$password]);
        }

        if($updated_user->favorite_teacher != $edit_user->favorite_teacher) {
            $last_teacher=User::where("id", $edit_user->favorite_teacher)->first();
            $present_teacher=User::where("id", $updated_user->favorite_teacher)->first();

            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Favorite Teacher", "old_data"=>$last_teacher==null?"N/A":$last_teacher->email, "new_data"=>$present_teacher==null?"N/A":$present_teacher->email]);
        }

        if($updated_user->credits != $edit_user->credits) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Credits", "old_data"=>$edit_user->credits==null?"N/A":$edit_user->credits, "new_data"=>$updated_user->credits==null?"N/A":$updated_user->credits]);
        }

        if($updated_user->real_sheet != $edit_user->real_sheet) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Former Progress Sheet", "old_data"=>$edit_user->real_sheet==null?"N/A":$edit_user->real_sheet, "new_data"=>$updated_user->real_sheet==null?"N/A":$updated_user->real_sheet]);
        }

        if($updated_user->dele_sheet != $edit_user->dele_sheet) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Progress File DELE", "old_data"=>$edit_user->dele_sheet==null?"N/A":$edit_user->dele_sheet, "new_data"=>$updated_user->dele_sheet==null?"N/A":$updated_user->dele_sheet]);
        }

        if($updated_user->electives_sheet != $edit_user->electives_sheet) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Progress File RW", "old_data"=>$edit_user->electives_sheet==null?"N/A":$edit_user->electives_sheet, "new_data"=>$updated_user->electives_sheet==null?"N/A":$updated_user->electives_sheet]);
        }

        if($updated_user->user_level != $edit_user->user_level) {
            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"User Level", "old_data"=>$edit_user->user_level==null?"N/A":$edit_user->user_level, "new_data"=>$updated_user->user_level==null?"N/A":$updated_user->user_level]);
        }

        if($edit_user->hasRole("student")){
            $student_data=($request->only(["subscription_status","subscription_plan","subscription_ends","user_level"]));

            $edit_user->user_level=$student_data["user_level"];

            User::where("id",$edit_user->id)->update(["user_level"=>$student_data["user_level"]]);

            $subscription = $edit_user->subscriptions()->orderBy("ends_at", "desc")->first();
            if($subscription){
                Subscription::where("id", $subscription->id)->update(["plan_name" => $student_data["subscription_plan"]]);

                if(($subscription->plan->name=="baselang_dele" && ($student_data["subscription_plan"]=="baselang_129" || $student_data["subscription_plan"]=="baselang_149")) || (($subscription->plan->name=="baselang_129" || $subscription->plan->name=="baselang_149") && $student_data["subscription_plan"]=="baselang_dele")) {
                    $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                    $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$edit_user->id)->get();

                    /*foreach($classes as $key => $class){
                        $class->removeZoom();
                        $class->delete();
                    }*/
                }

                if($subscription->plan->name != $student_data["subscription_plan"]) {
                    LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Subscription", "old_data"=>$subscription->plan->name==null?"N/A":$subscription->plan->name, "new_data"=>$student_data["subscription_plan"]==null?"N/A":$student_data["subscription_plan"]]);
                }
            }

            $buy_prebook=$edit_user->buy_prebooks->where("status",1)->first();
            $type=$request->get("type");
            $activation_date=$request->get("activation_date");

            if($buy_prebook){
                $new_data=($request->only(["type","status","activation_date"]));

                if($new_data["activation_date"] < $buy_prebook->activation_date){
                    return redirect()->back()->withErrors(["The expiration date of prebook can not be less than the start date"]);
                }
                $new_data["activation_date"]=\DateTime::createFromFormat('Y-m-d', $new_data["activation_date"])->sub(new \DateInterval('P1Y'))->format("Y-m-d");

                if($buy_prebook->activation_date != $new_data["activation_date"]) {
                    LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Expiration Date", "old_data"=>$buy_prebook->activation_date, "new_data"=>$new_data["activation_date"]==null?"N/A":$new_data["activation_date"]]);
                }

                if($new_data["type"]=="gold") {
                    $new_data["hours"]=15;
                }
                else{
                    $new_data["hours"]=5;
                }

                if($buy_prebook->type != $new_data["type"]) {
                    LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Type", "old_data"=>$buy_prebook->type, "new_data"=>$new_data["type"]==null?"N/A":$new_data["type"]]);
                }

                if($buy_prebook->status != $new_data["status"]) {
                    LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Status", "old_data"=>$buy_prebook->status==1?"Active":"Inactive", "new_data"=>$new_data["status"]==1?"Active":"Inactive"]);
                }

                $buy_prebook->update($new_data);
            }elseif($type!="none" && $activation_date) {
                if($type=="gold") {
                    $hours=15;
                }else{
                    $hours=5;
                }
                $buy_prebook = new BuyPrebook();
                $buy_prebook->user_id = $edit_user->id;
                $buy_prebook->type = $type;
                $buy_prebook->hours = $hours;
                $buy_prebook->status = 1;
                $buy_prebook->activation_date = \DateTime::createFromFormat('Y-m-d', $activation_date)->sub(new \DateInterval('P1Y'))->format("Y-m-d");
                $buy_prebook->save();
            }
        }

        $last_user_calendar=UserCalendar::orderBy("day", "ASC")->where("user_id",$edit_user->id)->get();
        UserCalendar::where("user_id",$edit_user->id)->delete();
        if($edit_user->hasRole("teacher")){

            $teacher_locations=$request->get('teacher_locations');

            foreach($edit_user->teacher_locations as $location) {
                $edit_user->teacher_locations()->detach($location->id);
            }

            if(isset($teacher_locations) && count($teacher_locations) > 0) {
                foreach($teacher_locations as $location) {
                    $edit_user->teacher_locations()->attach($location);
                }
            }

            if($updated_user->location != $edit_user->location) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Location", "old_data"=>$edit_user->location==null?"N/A":$edit_user->location, "new_data"=>$updated_user->location==null?"N/A":$updated_user->location]);
            }

            $dataExtra=($request->only(["gender","teaching_style","strongest_with","english_level"]));
            User::where("id",$user_id)->update($dataExtra);
            $updated_teacher=User::where("id",$user_id)->first();

            if($updated_teacher->gender != $edit_user->gender) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Gender", "old_data"=>$edit_user->gender==null?"N/A":$edit_user->gender, "new_data"=>$updated_teacher->gender==null?"N/A":$updated_teacher->gender]);
            }

            if($updated_teacher->teaching_style != $edit_user->teaching_style) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Teaching Style", "old_data"=>$edit_user->teaching_style==null?"N/A":$edit_user->teaching_style, "new_data"=>$updated_teacher->teaching_style==null?"N/A":$updated_teacher->teaching_style]);
            }

            if($updated_teacher->strongest_with != $edit_user->strongest_with) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Strongest with", "old_data"=>$edit_user->strongest_with==null?"N/A":$edit_user->strongest_with, "new_data"=>$updated_teacher->strongest_with==null?"N/A":$updated_teacher->strongest_with]);
            }

            if($updated_teacher->english_level != $edit_user->english_level) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"English Level", "old_data"=>$edit_user->english_level==null?"N/A":$edit_user->english_level, "new_data"=>$updated_teacher->english_level==null?"N/A":$updated_teacher->english_level]);
            }

            if($updated_teacher->is_deleteacher != $edit_user->is_deleteacher) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"DELE", "old_data"=>$edit_user->is_deleteacher==1?"Active":"Inactive", "new_data"=>$updated_teacher->is_deleteacher==1?"Active":"Inactive"]);
            }

            if($updated_teacher->block_online != $edit_user->block_online) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Block Online", "old_data"=>$edit_user->block_online==1?"Active":"Inactive", "new_data"=>$updated_teacher->block_online==1?"Active":"Inactive"]);
            }

            if($updated_teacher->block_prebook != $edit_user->block_prebook) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Block Prebook", "old_data"=>$edit_user->block_prebook==1?"Active":"Inactive", "new_data"=>$updated_teacher->block_prebook==1?"Active":"Inactive"]);
            }

            if($updated_teacher->youtube_url != $edit_user->youtube_url) {
                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Youtube", "old_data"=>$edit_user->youtube_url==null?"N/A":$edit_user->youtube_url, "new_data"=>$updated_teacher->youtube_url==null?"N/A":$updated_teacher->youtube_url]);
            }

            $user_calendar=$request->get("user_calendar");
            if($user_calendar){
                foreach($user_calendar as $j=>$user_interval_day){
                    foreach ($user_interval_day["from"] as $k=>$user_interval){
                        $from=\DateTime::createFromFormat("H:i",$user_interval,new \DateTimeZone($edit_user->timezone));
                        $from = Classes::fixTime($from);
                        if($from){
                            $start_from=$from->format("j");
                            $from=$from->setTimezone(new \DateTimeZone("UTC"));
                            if(!\DateTime::createFromFormat("H:i",$user_interval_day["till"][$k],new \DateTimeZone($edit_user->timezone))){
                                continue;
                            };


                            $till=\DateTime::createFromFormat("H:i",$user_interval_day["till"][$k],new \DateTimeZone($edit_user->timezone))->setTimezone(new \DateTimeZone("UTC"));
                            $till = Classes::fixTime($till);
                            $till = $till->format("H:i:s");

                            $day=$j;
                            if($start_from!=$from->format("j")){
                                $day++;
                                if($day==8){
                                    $day=1;
                                }
                            }

                            $calendar_data=["from"=>$from->format("H:i:s"),"till"=>$till,"day"=>$day,"user_id"=>$edit_user->id];

                            UserCalendar::create($calendar_data);
                        }

                    }
                }
            }

            $present_user_calendar=UserCalendar::orderBy("day", "ASC")->where("user_id",$edit_user->id)->get();

            $last_days=[];
            $new_days=[];
            foreach($last_user_calendar as $l_calendar) {
                foreach($present_user_calendar as $p_calendar) {
                    if(!isset($last_days[$l_calendar->day])){
                        $last_days[$l_calendar->day]='';
                    }
                    if(strpos($last_days[$l_calendar->day], $l_calendar->from." ".$l_calendar->till) === false) {
                        $last_days[$l_calendar->day].=$l_calendar->from." ".$l_calendar->till.", ";
                    }
                    if(!isset($new_days[$p_calendar->day])){
                        $new_days[$p_calendar->day]='';
                    }
                    if(strpos($new_days[$p_calendar->day], $p_calendar->from." ".$p_calendar->till) === false) {
                        $new_days[$p_calendar->day].=$p_calendar->from." ".$p_calendar->till.", ";
                    }
                }
            }

            $days_array=[1=>"Monday", 2=>"Tuesday", 3=>"Wednesday", 4=>"Thursday", 5=>"Friday", 6=>"Saturday", 7=>"Sunday"];
            if($last_days != $new_days) {
                if(count($last_days) > count($new_days)) {
                    foreach($last_days as $key => $calendar) {
                        $day=$days_array[$key];
                        if(isset($new_days[$key])) {
                            if($calendar!=$new_days[$key]) {
                                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Calendar", "old_data"=>$day.": ".$calendar." (UTC).", "new_data"=>$day.": ".$new_days[$key]." (UTC)."]);
                            }
                        }else{
                            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Calendar", "old_data"=>$day.": ".$calendar." (UTC).", "new_data"=>$day.": The schedules for this day have been removed."]);
                        }
                    }
                }else{
                    foreach($new_days as $key => $calendar) {
                        $day=$days_array[$key];
                        if(isset($last_days[$key])) {
                            if($calendar!=$last_days[$key]) {
                                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Calendar", "old_data"=>$day.": ".$last_days[$key]." (UTC).", "new_data"=>$day.": ".$calendar." (UTC)."]);
                            }
                        }else{
                            LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Calendar", "old_data"=>$day.": Previously there were no schedules on this day.", "new_data"=>$day.": ".$calendar." (UTC)."]);
                        }
                    }
                }
            }

            $user_interests=$request->get("interests");
            $new_interests=$request->get("new_interest");
            if($new_interests){
                foreach($new_interests as $new_interest){
                    $registred_interests=Interests::where("title",$new_interest)->first();
                    if(!$registred_interests){
                        $registred_interests=Interests::create(["title"=>ucfirst(strtolower($new_interest))]);
                    }
                    if(!is_array($user_interests)){
                        $user_interests=[];
                    }

                    $user_interests[]=$registred_interests->id;
                }
            }

            $verify_last_interests=[];
            foreach($edit_user->interests as $interest) {
                $verify_last_interests[]=$interest->id;
            }

            $edit_user->interests()->detach();

            $verify_present_interests=[];
                if($user_interests){
                foreach($user_interests as $user_interest){
                    $verify_present_interests[]=$user_interest;
                }
            }

            if($verify_last_interests != $verify_present_interests) {
                $last_interests=null;
                foreach($verify_last_interests as $interest) {
                    $int=Interests::where("id",$interest)->first();

                    if($int) {
                        $last_interests.=$int->title.", ";
                    }
                }

                $present_interests=null;
                foreach($verify_present_interests as $interest) {
                    $int=Interests::where("id",$interest)->first();

                    if($int) {
                        $present_interests.=$int->title.", ";
                    }
                }

                LogAdmin::create(["user_id"=>$edit_user->id, "admin_mail"=>$current_user->email, "field"=>"Interests", "old_data"=>$last_interests==null?"N/A":$last_interests, "new_data"=>$present_interests==null?"N/A":$present_interests]);
            }

            if($user_interests){
                foreach ($user_interests as $user_interest){
                    $edit_user->interests()->attach($user_interest);
                }
            };
            $prebooks_errors=collect();
            $till_time = false;
            $from_time = false;
            $calendar_time = false;
            $classes=[];
            try {
                $classes=Classes::where("teacher_id",$edit_user->id)->where("class_time",">",gmdate("Y-m-d H:i:s"))->get();
                foreach($classes as $class) {
                    $class_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time);
                    $class_time = Classes::fixTime($class_time);
                    $user_calendar=UserCalendar::where("user_id",$edit_user->id)->where("day",">",$class_time->format("N")-1)->where("day","<",$class_time->format("N")+1)->get();
                    $in_interval = false;
                    foreach($user_calendar as $calendar){
                        \Log::info("Prebook Class Time: " .var_export($class_time,true));
                        $diff = $calendar->day - $class_time->format("N");
                        $calendar_time = clone $class_time;
                        if($diff>0){
                            $calendar_time->add(new \DateInterval("P".$diff."D"));
                        } else {
                            $calendar_time->sub(new \DateInterval("P".$diff."D"));
                        }

                        \Log::info("Prebook Calendar Time: " .var_export($calendar_time,true));

                        $till_time = \DateTime::createFromFormat("Y-m-d H:i:s",$calendar_time->format("Y-m-d")." ".$calendar->till);
                        $from_time = \DateTime::createFromFormat("Y-m-d H:i:s",$calendar_time->format("Y-m-d")." ".$calendar->from);

                        \Log::info("Prebook Till Time: " .var_export($till_time,true));
                        \Log::info("Prebook From Time: " .var_export($from_time,true));

                        if($calendar->till<$calendar->from){
                            $till_time->add(new \DateInterval("P1D"));
                        }

                        if($till_time->format("U")>$class_time->format("U") && $class_time->format("U")>$from_time->format("U")){
                            $in_interval=true;
                            break;
                        }

                    }
                    if(!$in_interval) {
                        $prebooks_errors->push($class);
                    }
                }

                if(count($prebooks_errors)>0) {
                    try {
                        if (\App::environment('production')) {
                            \Mail::send('emails.prebook_calendar_error', ["teacher" => $edit_user, "current_user" => $current_user, "prebooks" => $prebooks_errors], function ($message) use ($edit_user) {
                                $message->subject("Prebook Calendar Error");
                                $message->to("niall@baselang.com", "Niall");
                            });
                        }
                    } catch (\Exception $e) {
                        Log::error('Cant send email: '.$e->getMessage());
                    }

                    return redirect()->route("admin_users_edit",["user_id"=>$user_id])->withErrors(["By editing the teacher's calendar, it has affected the pre-book of some students. An Email was send to niall@baselang.com"]);
                }

            } catch (\Exception $e) {
                \Log::error("Error Updating User Prebook Classes : " .$edit_user->email. " Updated By: ".$current_user->email." Error: ".$e->getMessage()."Line: ".$e->getLine()." Classes ".var_export($classes->toArray(),true));
            }

        }

        return redirect()->route("admin_users_edit",["user_id"=>$user_id])->with(["message_info"=>"User has been updated"]);
    }

    public function getCreate(){
        $roles = Role::get();
        return view("admin.users.create",["menu_active"=>"users","breadcrumb"=>true,"roles"=>$roles]);
    }

    public function csvSummary()
    {
        $users = User::get();
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($users, ['first_name', 'last_name', 'email'])->download();
    }

    public function getEdit($user_id){

        $edit_user=User::where("id",$user_id)->first();

        $roles = Role::get();

        $teachers = Role::where('name','teacher')->first()->users()->get();

        $subscription=$edit_user->subscriptions()->orderBy("ends_at","desc")->first();

        if(!$subscription){
            // $edit_user->updateSubscriptionInfo();
            $edit_user->refreshInformation();
            $date=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P7D"));
            Subscription::create(["user_id"=>$edit_user->id,"subscription_id"=>"BaseLang","status"=>"cancelled","plan_name"=>"baselang_149","starts_at"=>$date->format("Y-m-d"),"ends_at"=>$date->format("Y-m-d")]);
            $subscription=$edit_user->subscriptions()->orderBy("ends_at","desc")->first();
        }

        $roles_count=$edit_user->roles->count();
        if($roles_count==0){
            $edit_user->attachRole(Role::where("name","student")->first());
        }

        $interests=Interests::get();

        $locations=Location::orderBy("name", "ASC")->get();

        $current_subscription = $edit_user->getCurrentSubscription();

        $student_location = null;
        $student_inmersion = null;

        if($edit_user->location_id && $edit_user->subscriptionAdquired() && !$edit_user->isInmersionStudent()) {
            $student_location = Location::find($edit_user->location_id);
            if($student_location) {
                $student_location = $student_location->name;
            }
        }elseif($edit_user->location_id && $edit_user->isInmersionStudent()) {
            $student_inmersion = Location::find($edit_user->location_id);
            if($student_inmersion) {
                $student_inmersion = true;
            }
        }

        return view("admin.users.edit",["menu_active"=>"users","breadcrumb"=>true,"edit_user"=>$edit_user,"roles"=>$roles,"teachers"=>$teachers,"subscription"=>$subscription,"interests"=>$interests,"locations"=>$locations,"current_subscription"=>$current_subscription, "student_location"=>$student_location, "student_inmersion"=>$student_inmersion]);
    }

    public function getTrash($user_id){
        $edit_user=User::where("id",$user_id)->first();
        return view("admin.users.trash",["menu_active"=>"users","breadcrumb"=>true,"edit_user"=>$edit_user]);
    }

    public function addDeleTrial(Request $request){
        $user_id=$request->get("user_id");
        $plan=$request->get("plan");
        $user=User::where("id",$user_id)->first();

        if($user && ($plan=="baselang_129" || $plan=="baselang_149")){
            $user_subscription=$user->getCurrentSubscription();

            try {
                $result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            } catch (\Exception $e){
                \Log::error('Error cancel subscription (addDeleTrial baselang_149): '.$user->email);
            }

            if($user->active_dele_trial){
                $user->active_dele_trial->delete();
            }

            $active_dele_trial = new ActiveDeleTrial();
            $active_dele_trial->user_id = $user->id;
            $active_dele_trial->activation_day = $user_subscription->ends_at;
            $active_dele_trial->charge_dollar = 1;//For do charge dollar
            $active_dele_trial->save();

        }elseif($user && $plan=="baselang_hourly"){
            
            try {
                $result = \ChargeBee_Transaction::sale([
                    'amount' => '1.00',
                    'paymentMethodToken' => $user->payment_method_token,
                    'options' => [
                        'submitForSettlement' => True,
                    ]
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                \Log::info("Manually Add DELE Trial ".$user->email." ".$user->id);
            } catch(\Exception $e) {
                if(isset($result)){
                    \Log::error('Error Payment Method: '.var_export($result,true));
                } else {
                    \Log::error('Error Payment Method: '.var_export($e->getMessage(),true));
                }

                return redirect()->route("admin_users_edit",["user_id"=>$user_id])->withErrors(['The payment method rejected the charge. Please try again or contact user.']);
            }

            $user_subscription=$user->getCurrentSubscription();

            Subscription::where("user_id",$user->id)->delete();

            Subscription::create(["status"=>"active","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan_name"=>"baselang_dele_test","starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")]);

            $dele_trial_test = new DeleTrialTest();
            $dele_trial_test->user_id = $user->id;
            $dele_trial_test->completed = 0;
            $dele_trial_test->ends_at_last_subscription = $user_subscription->ends_at;
            $dele_trial_test->from = "baselang_hourly";
            $dele_trial_test->save();

            $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
            $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

            /*
            foreach($classes as $key => $class){
                $class->removeZoom();
                $class->delete();
            }
            */
        }elseif($user && $plan=="null"){
            try {
                $result = \ChargeBee_Transaction::sale([
                    'amount' => '1.00',
                    'paymentMethodToken' => $user->payment_method_token,
                    'options' => [
                        'submitForSettlement' => True,
                    ]
                ]);

                if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

                \Log::info("Manually Add DELE Trial ".$user->email." ".$user->id);
            } catch(\Exception $e) {
                if(isset($result)){
                    \Log::error('Error Payment Method: '.var_export($result,true));
                } else {
                    \Log::error('Error Payment Method: '.var_export($e->getMessage(),true));
                }

                return redirect()->route("admin_users_edit",["user_id"=>$user_id])->withErrors(['The payment method rejected the charge. Please try again or contact user.']);
            }

            Subscription::where("user_id",$user->id)->delete();

            Subscription::create(["status"=>"active","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan_name"=>"baselang_dele_test","starts_at"=>gmdate("Y-m-d"),"ends_at"=>\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d")]);

            $dele_trial_test = new DeleTrialTest();
            $dele_trial_test->user_id = $user->id;
            $dele_trial_test->completed = 0;
            $dele_trial_test->ends_at_last_subscription = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->add(new \DateInterval("P7D"))->format("Y-m-d");
            $dele_trial_test->from = "without_subscription";
            $dele_trial_test->save();

            $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
            $classes=Classes::where("class_time",">=",$class_time->format("Y-m-d H:i:s"))->where("user_id",$user->id)->get();

            /*foreach($classes as $key => $class){
                $class->removeZoom();
                $class->delete();
            }*/
        }

        return redirect()->route("admin_users_edit",["user_id"=>$user_id])->with(["message_info"=>"DELE trial added successfully"]);
    }

}
