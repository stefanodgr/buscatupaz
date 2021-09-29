<?php

namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class LessonsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "lessons");
    }

    public function getIndex(){
        return view("admin.lessons.list",["breadcrumb"=>true]);
    }

    public function removePDF(Request $request){
        $lesson_id=$request->get("lesson_id");

        try {
            Storage::disk("uploads")->delete('/assets/lessons/pdf/'.$lesson_id.".pdf");
        } catch (\Exception $e){
            Log::error("Error, removing PDF". $lesson_id);
            return redirect()->route("admin_lessons_edit",["lesson_id"=>$lesson_id])->withErrors(["message_info"=>"PDF file Doesn't exist"]);
        }


        return redirect()->route("admin_lessons_edit",["lesson_id"=>$lesson_id])->with(["message_info"=>"PDF Deleted"]);

    }

    public function getList(){
        $lessons = Lesson::get();

        $lessons_list=[];
        foreach($lessons as $lesson){
            if($lesson->level){
                $lessons_list[]=[$lesson->name." <span>".$lesson->slug."</span>",$lesson->order,$lesson->level->name.": ".($lesson->level->type=="elective"?"Elective":($lesson->level->type=="real"?"Real World":($lesson->level->type=="grammar"?"DELE - Grammar":($lesson->level->type=="skills"?"DELE - Skills Improvement":($lesson->level->type=="test"?"DELE - Test-Prep":($lesson->level->type=="intros"?"DELE - Intro":"GL")))))),$lesson->enabled?"Active":"Inactive",'<a href="'.route("admin_lessons_edit",["lesson_id"=>$lesson->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_lessons_trash",["lesson_id"=>$lesson->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a>'];
            } else {
                $lessons_list[]=[$lesson->name." <span>".$lesson->meta_title.": ".$lesson->meta_description."</span>",$lesson->order,"N.A",$lesson->enabled?"Active":"Inactive",'<a href="'.route("admin_lessons_edit",["lesson_id"=>$lesson->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="'.route("admin_lessons_trash",["lesson_id"=>$lesson->id]).'"><i class="fa fa-trash" aria-hidden="true"></i></a>'];
            }
        }

        return response()->json(['data' => $lessons_list]);
    }

    public function getEdit($lesson_id){
        $lesson=Lesson::where("id",$lesson_id)->first();
        $levels=Level::get();
        return view("admin.lessons.edit",["breadcrumb"=>true,"lesson"=>$lesson,"levels"=>$levels]);
    }

    public function delete(Request $request){
        $lesson_id=$request->get("lesson_id");

        Lesson::where("id",$lesson_id)->delete();

        return redirect()->route("admin_lessons")->with(["message_info"=>"Lesson has been deleted"]);
    }

    public function create(Request $request){
        $data=($request->only(["name","slug","meta_title","meta_description","level_id","enabled","order","description","readingn","listeningn","writingn","speakingn","homework_audio","homework_text","externalurl","is_free"]));
        $slug=$data["slug"];

        if(!empty($slug)){
            $checkSlug=Lesson::where("slug",$slug)->first();
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
                $checkSlug=Lesson::where("slug",$testSlug)->first();
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

        if(!$data["is_free"]){
            $data["is_free"]=0;
        } else {
            $data["is_free"]=1;
        }

        if(!$data["homework_text"]){
            $data["homework_text"]=0;
        } else {
            $data["homework_text"]=1;
        }

        if(!$data["homework_audio"]){
            $data["homework_audio"]=0;
        } else {
            $data["homework_audio"]=1;
        }

        $lesson=Lesson::create($data);

        if($request->file('lesson_pdf')){
            $lesson_pdf = Storage::disk("uploads")->putFileAs('/assets/lessons/pdf', $request->file('lesson_pdf'),$lesson->id.".pdf");
        }

        return redirect()->route("admin_lessons_edit",["lesson_id"=>$lesson->id])->with(["message_info"=>"Lesson created"]);
    }

    public function update(Request $request){
        $data=($request->only(["name","slug","meta_title","meta_description","level_id","enabled","order","description","readingn","listeningn","writingn","speakingn","homework_audio","homework_text","externalurl","is_free"]));
        $lesson_id=$request->get("lesson_id");
        $lesson=Lesson::where("id",$lesson_id)->first();
        $slug=$data["slug"];

        if($request->file('lesson_pdf')){
            $lesson_pdf = Storage::disk("uploads")->putFileAs('/assets/lessons/pdf', $request->file('lesson_pdf'),$lesson->id.".pdf");
        }
        if(!empty($slug)){
            $checkSlug=Lesson::where("slug",$slug)->first();
            if($checkSlug && $checkSlug->id!=$lesson->id){
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
                $checkSlug=Lesson::where("slug",$testSlug)->first();
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

        if(!$data["homework_text"]){
            $data["homework_text"]=0;
        } else {
            $data["homework_text"]=1;
        }


        if(!$data["is_free"]){
            $data["is_free"]=0;
        } else {
            $data["is_free"]=1;
        }

        if(!$data["homework_audio"]){
            $data["homework_audio"]=0;
        } else {
            $data["homework_audio"]=1;
        }

        Lesson::where("id",$lesson_id)->update($data);

        return redirect()->route("admin_lessons_edit",["lesson_id"=>$lesson_id])->with(["message_info"=>"Lesson updated"]);
    }

    public function getTrash($lesson_id){
        $lesson=Lesson::where("id",$lesson_id)->first();
        return view("admin.lessons.trash",["breadcrumb"=>true,"lesson"=>$lesson]);
    }

    public function getCreate(){
        $levels=Level::get();
        return view("admin.lessons.create",["breadcrumb"=>true,"levels"=>$levels]);
    }


}
