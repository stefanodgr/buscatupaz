<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Level;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ElectiveController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        View::share('menu_active', "electives");
    }

    public function chargeElective(Request $request){
        $user = User::getCurrent();
        $data=$request->only(["level_id"]);

        $level=Level::where("id",$data["level_id"])->where("type","elective")->where("enabled",1)->where("price","<>",0)->first();

        if(!$level){
            return redirect()->route("electives");
        }

        $user_level=$user->levels()->where("level_id",$level->id)->where("paid",1)->first();
        if($user_level){
            return redirect()->route("elective_level",["level_slug"=>$level->slug])->with(["message_info"=>"You already have this elective"]);
        }


        try {
            $result = \ChargeBee_Transaction::createAuthorization([
                'amount' => $level->price.'',
                'paymentMethodToken' => $user->payment_method_token,
                'descriptor' => [
                    'name' => 'BASELANG    *Elective '
                ],
                'options' => [
                    'submitForSettlement' => True,
                    'paypal' => [
                        'description'=> 'BaseLang Elective '.$level->name
                    ]
                ]
            ]);

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            $user_level=$user->levels()->where("level_id",$level->id)->first();
            if(!$user_level){
                $user->levels()->save($level);
            }

            $user->levels()->updateExistingPivot($level->id,["paid"=>1,"transaction_id"=>$result->transaction->id]);

        } catch (\Exception $e){
            Log::error("Error buying elective: ".$e->getMessage());
            return redirect()->back()->withErrors(["Your payment method was declined"]);
        }

        return redirect()->route("elective_level",["level_slug"=>$level->slug])->with(["message_info"=>"This elective has been activated"]);

    }

    public function buyElective($level_slug){
        $user = User::getCurrent();

        $level=Level::where("slug",$level_slug)->where("type","elective")->where("enabled",1)->first();
        if(!$level){
            return redirect()->route("electives");
        }

        $user_level=$user->levels()->where("level_id",$level->id)->where("paid",1)->first();
        if($user_level){

            return redirect()->route("elective_level",["level_slug"=>$level->slug])->with(["message_info"=>"This elective has been activated"]);
        }

        return view("electives.buy",["level"=>$level,"user_level"=>$user_level,"breadcrumb"=>true]);
    }

    public function getLevel($level_slug){
        $user = User::getCurrent();

        $level=Level::where("slug",$level_slug)->where("type","elective")->where("enabled",1)->first();
        $user_level=$user->levels()->where("level_id",$level->id)->where("paid",1)->first();
        if(!$level){
            return redirect()->route("electives");
        }


        $level->free_lessons=0;
        foreach($level->lessons as &$lesson){
            $lesson->completed=false;
            $lesson_completed=$user->lessons()->where("lesson_id",$lesson->id)->where("completed",1)->first();
            if($lesson_completed){
                $lesson->completed=true;
            };

            if($lesson->is_free){
                $level->free_lessons++;
            }

        }

        return view("electives.level",["level"=>$level,"user_level"=>$user_level,"breadcrumb"=>true]);
    }

    public function saveLesson(Request $request){
        $user = User::getCurrent();
        $lesson_id=$request->input("lesson");

        $lesson = Lesson::where("id",$lesson_id)->where("enabled",1)->first();
        if(!$lesson){
            return response()->json(['status' => 'sucess', 'lesson_state' => 0]);
        }

        $level = Level::where("id",$lesson->level_id)->where("enabled",1)->first();
        if(!$level){
            return response()->json(['status' => 'sucess', 'lesson_state' => 0]);
        }

        $user_lesson = $user->lessons()->where("lesson_id",$lesson_id)->first();

        $user_level=$user->levels()->where("level_id",$lesson->level_id)->where("paid",1)->first();
        /*if(!$user_level && !$lesson->is_free && $level->price!=0 && !$user->buy_prebooks()->first() && !$user->buy_prebooks()->first()->type=="gold"){
            return response()->json(['status' => 'sucess', 'lesson_state' => 0]);
        }*/


        if(!$user_lesson){
            $user->lessons()->save($lesson);
            $user->lessons()->updateExistingPivot($lesson_id,["completed"=>1,"finished_at"=>gmdate("Y-m-d H:i:s")]);
            return response()->json(['status' => 'sucess', 'lesson_state' => 1]);
        } else {
            if($user_lesson->pivot->completed){
                $user->lessons()->updateExistingPivot($lesson_id,["completed"=>0,"finished_at"=>gmdate("Y-m-d H:i:s")]);
                return response()->json(['status' => 'sucess', 'lesson_state' => 0]);
            } else {
                $user->lessons()->updateExistingPivot($lesson_id,["completed"=>1,"finished_at"=>gmdate("Y-m-d H:i:s")]);
                return response()->json(['status' => 'sucess', 'lesson_state' => 1]);
            }

        }

    }

    public function getLesson($level_slug,$lesson_slug){
        $user = User::getCurrent();
        $level=Level::where("slug",$level_slug)->where("enabled",1)->where("type","elective")->first();


        if(!$level){
            return redirect()->route("electives");
        }

        $lesson=$level->lessons()->where("slug",$lesson_slug)->where("enabled",1)->first();
        if(!$lesson){
            return redirect()->route("elective_level",["level_slug"=>$level_slug]);
        }

        $user_level=$user->levels()->where("level_id",$level->id)->where("paid",1)->first();
        /*if(!$user_level && !$lesson->is_free && $level->price!=0 && !$user->buy_prebooks()->first() && !$user->buy_prebooks()->first()->type=="gold"){
            return redirect()->route("elective_level",["level_slug"=>$level_slug]);
        };*/

        $user_lesson = $user->lessons()->where("lesson_id",$lesson->id)->first();
        return view("electives.lesson",["level"=>$level,"lesson"=>$lesson,"user_lesson"=>$user_lesson,"breadcrumb"=>true]);

    }

    public function saveHomework(Request $request){
        $user = User::getCurrent();

        $data=$request->only(["lesson","text_homework"]);

        $lesson_id=$data["lesson"];

        $lesson=Lesson::where("id",$lesson_id)->where("enabled",1)->first();
        if(!$lesson){
            return response()->json(['status' => 'sucess']);
        }

        $level=Level::where("id",$lesson->level_id)->where("enabled",1)->first();
        if(!$level){
            return response()->json(['status' => 'sucess']);
        }

        $user_level=$user->levels()->where("level_id",$lesson->level_id)->where("paid",1)->first();
        /*if(!$user_level && !$lesson->is_free && $level->price!=0 && !$user->buy_prebooks()->first() && !$user->buy_prebooks()->first()->type=="gold"){
            return response()->json(['status' => 'sucess']);
        }*/

        if(isset($request->audio_data)){
            if(!file_exists(public_path().'/assets/homeworks/user_audio/'.$lesson_id."_".$user->id.'.wav')){
                $request->audio_data->storeAs('assets/homeworks/user_audio', $lesson_id."_".$user->id.'.wav', 'uploads');
            }
        }

        if(!empty($data["text_homework"])){
            $user_lesson=$user->lessons()->where("lesson_id",$lesson_id)->first();
            if(!$user_lesson){
                $user->lessons()->save($lesson);
                $user->lessons()->updateExistingPivot($lesson_id,["homework"=>$data["text_homework"]]);
            } elseif(!isset($user_lesson->pivot->homework)){
                $user->lessons()->updateExistingPivot($lesson_id,["homework"=>$data["text_homework"]]);
            };

        }

        return response()->json(['status' => 'sucess']);
    }

    public function getLessons(){
        $user = User::getCurrent();

        $levels=Level::where("type","elective")->where("enabled",1)->orderBy("level_order","asc")->get();
        foreach($levels as $level){
            $level_ids[] = $level->id;
        }

        $lesson_ids = array();
        $lessonsids_levelids_mapping = array();
        $level_lessons = Lesson::whereIn("level_id",$level_ids)->where("enabled",1)->get();
        $user_lessons_mapping = array();
        foreach($level_lessons as $level_lesson) {
            $lesson_ids[] = $level_lesson->id;
            $lessonsids_levelids_mapping[$level_lesson->id] = $level_lesson->level_id;
        }

        $user_lessons_mapping = array();
        $user_lessons = $user->lessons()->whereIn("lesson_id",$lesson_ids)->where("completed",1)->get();
        foreach($user_lessons as $user_lesson) {
            $level_id = $lessonsids_levelids_mapping[$user_lesson->id];
            if(array_key_exists($level_id, $user_lessons_mapping)){
                $user_lesson_array = $user_lessons_mapping[$level_id]; 
                array_push($user_lesson_array, $user_lesson);
                $user_lessons_mapping[$level_id] = $user_lesson_array;
            } else{
                $user_lesson_array = array();
                array_push($user_lesson_array, $user_lesson);
                $user_lessons_mapping[$level_id] = $user_lesson_array;
            }
        }

        foreach($levels as &$level){
            if(sizeof($user_lessons_mapping)>0 && array_key_exists($level->id, $user_lessons_mapping)) {
                $level->completed = sizeof($user_lessons_mapping[$level->id]);
            } else {
                $level->completed = 0;
            }
        };

        return view("electives.lessons",["levels"=>$levels]);
    }

}
