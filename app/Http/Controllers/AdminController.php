<?php

namespace App\Http\Controllers;



use App\User;
use Illuminate\Support\Facades\Auth;
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
        View::share('menu_active', "basic_info");
    }



    public function getAdminDashboard(){
        return view("main.admin_dashboard",["menu_active"=>"dashboard"]);
    }


    public function getAdminUsersList(){
        $users = User::get();

        $user_list=[];
        foreach($users as $user){
            $roles="";
            foreach($user->roles as $k=>$user_rol){
                $roles.="<span>".($user_rol->name)."</span>";
                if($k+1!=count($user->roles)){
                    $roles.=" ";
                }

            }
            if($roles==""){
                $roles="<span>student</span>";
            }
            $user_list[]=[$user->email." <span>".$user->first_name." ".$user->last_name."</span>",$roles,$user->created_at->format("Y/m/d"),$user->activated=="1"?"Activo":"Inactivo",'<a href="'.route("admin_users_edit",["user_id"=>$user->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_users_trash",["user_id"=>$user->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a><a href="'.route("user_impersonate",["user_id"=>$user->id]).'"><i class="fa fa-eye" aria-hidden="true"></i></a>'];
        }

        return response()->json(['data' => $user_list]);
    }

    public function getAdminUsers(){
        return view("admin.users.list",["menu_active"=>"users","breadcrumb"=>true]);
    }

}
