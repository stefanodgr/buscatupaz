<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;

class LevelsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "levels");
    }

    public function getIndex(){
        return view("admin.levels.list",["menu_active"=>"levels","breadcrumb"=>true]);
    }


    public function getList(){
        $levels = Level::get();

        $levels_list=[];
        foreach($levels as $level){
            $levels_list[]=[$level->name,$level->level_order,$level->type=="elective"?"Elective":($level->type=="real"?"Real World":($level->type=="grammar"?"DELE - Grammar":($level->type=="skills"?"DELE - Skills Improvement":($level->type=="test"?"DELE - Test-Prep":($level->type=="intros"?"DELE - Intro":"GL"))))),$level->enabled?"Active":"Inactive",'<a href="'.route("admin_levels_edit",["level_id"=>$level->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_levels_trash",["level_id"=>$level->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a>'];
        }

        return response()->json(['data' => $levels_list]);
    }

    public function getEdit($level_id){
        $level=Level::where("id",$level_id)->first();
        return view("admin.levels.edit",["menu_active"=>"levels","breadcrumb"=>true,"level"=>$level]);
    }

    public function delete(Request $request){
        $level_id=$request->get("level_id");

        Level::where("id",$level_id)->delete();

        return redirect()->route("admin_levels")->with(["message_info"=>"Level deleted"]);
    }

    public function create(Request $request){
        $data=($request->only(["name","slug","meta_title","meta_description","exam_lesson_id","enabled","level_order","description","type","youtube_link","price"]));
        $slug=$data["slug"];

        if(!empty($slug)){
            $checkSlug=Level::where("slug",$slug)->first();
            if($checkSlug){
                //slug exist
                return redirect()->back()->withErrors(["Slug already in use"]);
            } else {
                $slug = str_replace(' ', '-', $slug); // Replaces all spaces with hyphens.
                $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.
            }
        } else {
            $slug = str_replace(' ', '-', $data["name"]); // Replaces all spaces with hyphens.
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.

            $testSlug=$slug;
            $i=1;
            do {
                $checkSlug=Level::where("slug",$testSlug)->first();
                if(!$checkSlug){
                    $testSlug=$slug."-(".$i.")";
                    $i++;
                }

            }while($checkSlug);
            $slug=$testSlug;
        }

        $data["slug"]=$slug;


        if(!$data["enabled"]){
            $data["enabled"]=0;
        } else {
            $data["enabled"]=1;
        }

        $level=Level::create($data);

        return redirect()->route("admin_levels_edit",["level_id"=>$level->id])->with(["message_info"=>"Level created"]);

    }
    public function update(Request $request){
        $data=($request->only(["name","slug","meta_title","meta_description","exam_lesson_id","enabled","level_order","description","type","price","youtube_link","desc_sales","desc_included","desc_whofor"]));
        $level_id=$request->get("level_id");
        $level=Level::where("id",$level_id)->first();
        $slug=$data["slug"];

        if(!empty($slug)){
            $checkSlug=Level::where("slug",$slug)->first();
            if($checkSlug && $checkSlug->id!=$level->id){
                //slug exist
                return redirect()->back()->withErrors(["Slug already in use"]);
            } elseif(!$checkSlug) {
                $slug = str_replace(' ', '-', $slug); // Replaces all spaces with hyphens.
                $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.
            }
        } else {
            //generate slug

            $slug = str_replace(' ', '-', $data["name"]); // Replaces all spaces with hyphens.
            $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug); // Removes special chars.

            $testSlug=$slug;
            $i=1;
            do {
                $checkSlug=Level::where("slug",$testSlug)->first();
                if(!$checkSlug){
                    $testSlug=$slug."-(".$i.")";
                    $i++;
                }

            }while($checkSlug);
            $slug=$testSlug;
        }

        $data["slug"]=$slug;


        if(!$data["enabled"]){
            $data["enabled"]=0;
        } else {
            $data["enabled"]=1;
        }

        Level::where("id",$level_id)->update($data);

        return redirect()->route("admin_levels_edit",["level_id"=>$level_id])->with(["message_info"=>"Level updated"]);
    }

    public function getTrash($level_id){
        $level=Level::where("id",$level_id)->first();
        return view("admin.levels.trash",["menu_active"=>"levels","breadcrumb"=>true,"level"=>$level]);
    }

    public function getCreate(){
        return view("admin.levels.create",["menu_active"=>"levels","breadcrumb"=>true]);
    }


}
