<?php

namespace App\Http\Controllers\Admin;

use App\Models\Location;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class LocationsController extends Controller
{
    public function __construct() {
        View::share('menu_active', "locations");
    }

    public function getIndex() {
        return view("admin.locations.list", ["breadcrumb" => true]);
    }

    public function getList() {
        $locations = Location::all();

        $locations_list = [];
        foreach($locations as $location) {
            $locations_list[] = [$location->id, ucwords(strtolower($location->name)), $location->timezone,'<a href="'.route("admin_locations_edit",["location_id"=>$location->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a> <a href="'.route("admin_locations_trash",["location_id"=>$location->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a>'];
        }

        return response()->json(['data' => $locations_list]);
    }

    public function getCreate() {
        return view("admin.locations.create", ["breadcrumb" => true]);
    }

    public function create(Request $request) {
        $data = $request->only(["name","timezone","time_message","email_message","survey","price"]);

        $data["name"] = strtolower($data["name"]);

        $check_location = Location::where("name", $data["name"])->first();
        
        if($check_location) {
    		return redirect()->back()->withErrors(["The entered location already exists"]);
        }

        $location = Location::create($data);

        return redirect()->route("admin_locations_edit", ["location_id" => $location->id])->with(["message_info"=>"Location created"]);
    }

    public function getEdit($location_id) {
        $location = Location::find($location_id);

        if(!$location){
            return redirect()->route("admin_locations")->with(["message_info" => "Location ".$location_id." does not exist"]);
        }

        return view("admin.locations.edit",["breadcrumb" => true, "location" => $location]);
    }

    public function update(Request $request) {
        $data = $request->only(["name","timezone","time_message","email_message","survey","price"]);
        $data["name"] = strtolower($data["name"]);
        $check_location = Location::where("name", $data["name"])->first();

        $location_id = $request->get("location_id");
        $location = Location::find($location_id);


        if($check_location && $check_location->id != $location->id) {
        	return redirect()->back()->withErrors(["The entered location already exists"]);
        }

        Location::where("id", $location_id)->update($data);

        return redirect()->route("admin_locations_edit",["location_id"=>$location_id])->with(["message_info"=>"Location updated"]);
    }

    public function getTrash($location_id) {
        $location = Location::find($location_id);

        if(!$location){
            return redirect()->route("admin_locations")->with(["message_info" => "Location ".$location_id." does not exist"]);
        }

        return view("admin.locations.trash",["breadcrumb" => true, "location" => $location]);
    }

    public function delete(Request $request) {
        $location_id = $request->get("location_id");

        Location::where("id",$location_id)->delete();

        return redirect()->route("admin_locations")->with(["message_info"=>"Location deleted"]);
    }

    public function getIndexUsers() {
        return view("admin.locations.list_users", ["breadcrumb" => true]);
    }

    public function getListUsers(){
        $users = Role::where('name','student')->first()->users()->where("location_id","<>",null)->get();

        $user_list=[];
        foreach($users as $user){
            $roles="";
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
            $user_list[]=[$user->email." <span>".$user->first_name." ".$user->last_name."</span>",$roles,$user->created_at->format("Y/m/d"),$user->activated=="1"?"Active":"Inactive",'<a href="'.route("admin_users_edit",["user_id"=>$user->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_users_trash",["user_id"=>$user->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a><a href="'.route("user_impersonate",["user_id"=>$user->id]).'"><i class="fa fa-eye" aria-hidden="true"></i></a>'];
        }

        return response()->json(['data' => $user_list]);
    }

}
