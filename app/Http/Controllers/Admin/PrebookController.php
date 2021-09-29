<?php

namespace App\Http\Controllers\Admin;

use App\Models\BuyPrebook;
use App\Models\Classes;
use App\Models\Prebook;
use App\Models\Role;
use App\Models\UserCalendar;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class PrebookController extends Controller
{
    public function __construct(){
        View::share('menu_active', "prebook");
    }

    public function getIndex(){
        return view("admin.prebook.list",["breadcrumb"=>true]);
    }

    public function getList(){
        $buy_prebooks = BuyPrebook::all();

        $buy_prebooks_list=[];
        foreach($buy_prebooks as $buy_prebook){
            if($buy_prebook->student){
                $buy_prebooks_list[]=[$buy_prebook->student->email." <span>".$buy_prebook->student->first_name." ".$buy_prebook->student->last_name."</span>", ucfirst($buy_prebook->type), $buy_prebook->created_at->format("Y-m-d"), \DateTime::createFromFormat('Y-m-d', $buy_prebook->activation_date)->add(new \DateInterval('P1Y'))->format("Y-m-d"),'<a href="'.route("admin_prebooks_edit",["buy_prebook_id"=>$buy_prebook->id]).'"><i class="fa fa-pencil" aria-hidden="true"></i></a>'];
            }
        }

        return response()->json(['data' => $buy_prebooks_list]);
    }

    public function getEdit($buy_prebook_id){

        $edit_buy_prebook=BuyPrebook::where("id",$buy_prebook_id)->first();

        if(!$edit_buy_prebook) {
        	return redirect()->route("admin_prebooks")->withErrors(["The prebook ".$buy_prebook_id." is not registered"]);
        }

        $prebooks=Prebook::where("user_id",$edit_buy_prebook->student->id)->get();

        return view("admin.prebook.edit",["breadcrumb"=>true,"edit_buy_prebook"=>$edit_buy_prebook,"prebooks"=>$prebooks]);
    }

    public function update(Request $request){
        $data=($request->only(["type","status","activation_date"]));
        $buy_prebook_id=$request->get("buy_prebook_id");
        $activation_date=$request->get("activation_date");
        $buy_prebook=BuyPrebook::where("id",$buy_prebook_id)->first();

        if($activation_date < $buy_prebook->activation_date){
        	return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$buy_prebook_id])->withErrors(['The expiration date can not be less than the start date.']);
        }
        $data["activation_date"]=\DateTime::createFromFormat('Y-m-d', $data["activation_date"])->sub(new \DateInterval('P1Y'))->format("Y-m-d");

        if($data["type"]=="gold") {
        	$data["hours"]=15;
        }
        else{
        	$data["hours"]=5;
        }

        $buy_prebook->update($data);

        return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$buy_prebook_id])->with(["message_info"=>"Prebook updated"]);
    }

    public function cancelPrebook(Request $request){
        $prebook=$request->input("prebook");
        $buy_prebook_id=$request->input("buy_prebook_id");
        $prebook=Prebook::where("id",$prebook)->first();
        
        if(!$prebook){
        	return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$buy_prebook_id])->withErrors(["Prebook is already cancelled"]);
        }

        $current_day=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new \DateTimeZone("UTC"));
        $current_day=$current_day->format("N");

        $new_day=null;
        $days=1;

        if($current_day==$prebook->day) {
            $new_day=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new \DateTimeZone("UTC"))->format("Y-m-d");
        }else{
            while($current_day!=$prebook->day) {
                $day=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new \DateTimeZone("UTC"))->add(new \DateInterval("P".$days."D"));
                $new_day=$day->format("Y-m-d");
                $current_day=$day->format("N");
                $days++;
            }
        }

        $day=\DateTime::createFromFormat("Y-m-d H:i:s",$new_day." ".$prebook->hour)->setTimezone(new \DateTimeZone("UTC"))->add(new \DateInterval("P7D"));
        $class=Classes::where("user_id",$prebook->user_id)->where("class_time",$day->format("Y-m-d H:i:s"))->first();
        if($class) {
            $class->delete();
        }

        $day=$day->add(new \DateInterval("P7D"));
        $class=Classes::where("user_id",$prebook->user_id)->where("class_time",$day->format("Y-m-d H:i:s"))->first();
        if($class) {
            $class->delete();
        }

        $prebook->delete();

        return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$buy_prebook_id])->with(["message_info"=>"The prebook has been cancelled"]);
    }

	public function getNewPrebook($user_id, $teacher_id=false){
        $user = User::where("id",$user_id)->first();

        if(!$user) {
            return redirect()->route("admin_prebooks")->withErrors(["The user ".$user_id." has not registered"]);
        }

        if(!$user->buy_prebooks()->first()) {
            return redirect()->route("admin_prebooks")->withErrors(["The user ".$user_id." has not purchased the prebook"]);
        }

        $teacher = false;

        $current_subscription = $user->getCurrentSubscriptionType();
        if($current_subscription && ($current_subscription == "dele_real" || $current_subscription == "dele")){
            $subscriptionType = "dele";
        }
        elseif($current_subscription && $current_subscription == "real"){
			$subscriptionType = "real";
        }
        else{
        	return redirect()->route("admin_prebooks")->withErrors(["The user ".$user->email." does not have an active subscription"]);
        }
        
        if($user->favorite_teacher){
            $teachers = Role::where('name','teacher')->first()->users()->where("id","<>",$user->favorite_teacher)->where("activated",1)->where("block_prebook",0)->orderBy("first_name","asc")->get();
        } else{
            $teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->where("block_prebook",0)->orderBy("first_name","asc")->get();
        }

        if($subscriptionType=="real"){
            $teachers=$teachers->where("is_deleteacher",0);
            if($teacher_id){
                $teacher=User::where("id",$teacher_id)->where("is_deleteacher",0)->where("activated",1)->first();
            }

        } else {
            $teachers=$teachers->where("is_deleteacher",1);
            if($teacher_id){
                $teacher=User::where("id",$teacher_id)->where("is_deleteacher",1)->where("activated",1)->first();
            }
        }

        if($teacher_id && !$teacher){
            return redirect()->route("admin_prebooks");
        }

        $array_one=collect();
        $array_two=collect();
        $array_final=collect();
        foreach($teachers as $teacher){
            if($teacher->getEvaluatedCurrent()){
                $teacher->evaluation_student=$teacher->getEvaluatedCurrent()->evaluation;
                $array_one->push($teacher);
            } else{
                $teacher->evaluation_student=0;
                $array_two->push($teacher);
            }
        }
        $array_one=$array_one->sortByDesc('evaluation_student');
        
        if($user->favorite_teacher){
            $teacher=User::where("id",$user->favorite_teacher)->first();
            $array_final->push($teacher);
        }

        foreach($array_one as $item){
            $array_final->push($item);
        }

        foreach($array_two as $item){
            $array_final->push($item);
        }

        return view("admin.prebook.new",["menu_active"=>"prebook","teacher_id"=>$teacher_id,"teachers"=>$array_final,"user_id"=>$user_id,"current_user"=>$user]);
	}

    public function csvSummary()
    {
        $prebook = Prebook::all();
        $prebook=$prebook->unique('user_id');

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($prebook, ['student.first_name', 'student.last_name', 'student.email','teacher.first_name','teacher.last_name','teacher.email','type'])->download();
    }

    public function getCalendar($user_id, $teacher_id=false){
        $user = User::where("id",$user_id)->first();
        $teacher = User::where("id",$teacher_id)->first();
        $teacher_prebooks = Prebook::where("teacher_id",$teacher_id)->get();
        $start_time = \DateTime::createFromFormat("U",strtotime('monday this week'),new \DateTimeZone("UTC"));
        $ownhours=0;
        $subscriptionType = $user->getCurrentSubscriptionType();
        if(!$subscriptionType) {
            $subscriptionType = "real";
        }

        $calendar=[];
        if($teacher) {
            $teacher_calendar = $teacher->getCalendar;
            for($i=1;$i<=7;$i++) {

                if(!isset($calendar[$i])){
                    $calendar[$i] = new \stdClass();
                    $calendar[$i]->percentage=0;
                    $calendar[$i]->busy=0;
                    $calendar[$i]->classes=0;
                    $calendar[$i]->ownhours=[];
                    $calendar[$i]->hours=[];
                }

                foreach($teacher_calendar->where("day",$i) as $calendar_item) {
                    $from=\DateTime::createFromFormat("Y-m-d H:i:s",$start_time->format("Y-m-d").' '.$calendar_item->from)->setTimezone(new \DateTimeZone($user->timezone));
                    $from = Classes::fixTime($from);

                    $till=\DateTime::createFromFormat("Y-m-d H:i:s",$start_time->format("Y-m-d").' '.$calendar_item->till)->setTimezone(new \DateTimeZone($user->timezone));
                    $till = Classes::fixTime($till);

                    if($till<$from){
                        $till->add(new \DateInterval("P1D"));
                    }

                    while($from < $till) {
                        $time=clone $from;
                        $time=$time->setTimezone(new \DateTimeZone("UTC"));
                        $checkPrebook=$teacher_prebooks->where("day",$time->format("N"))->where("hour",$time->format("H:i:s"))->first();

                        if(!isset($calendar[$from->format("N")])){
                            $calendar[$from->format("N")] = new \stdClass();
                            $calendar[$from->format("N")]->percentage=0;
                            $calendar[$from->format("N")]->busy=0;
                            $calendar[$from->format("N")]->classes=0;
                            $calendar[$from->format("N")]->ownhours=[];
                            $calendar[$from->format("N")]->hours=[];
                        }


                        if(!$checkPrebook){
                            $calendar[$from->format("N")]->hours[]=$from->format("h:iA");
                        }elseif($checkPrebook->user_id==$user->id){
                            $calendar[$from->format("N")]->hours[]=$from->format("h:iA");
                            $prebookTime = \DateTime::createFromFormat("Y-m-d H:i:s",$start_time->format("Y-m-d").' '.$checkPrebook->hour)->setTimezone(new \DateTimeZone($user->timezone));
                            $calendar[$from->format("N")]->ownhours[]=$from->format("h:iA");
                            $ownhours++;
                            $calendar[$from->format("N")]->busy++;
                        } else {
                            $calendar[$from->format("N")]->busy++;
                        }

                        $calendar[$from->format("N")]->classes++;
                        $from->add(new \DateInterval('PT30M'));

                        if($subscriptionType=="dele"){
                            $from->add(new \DateInterval('PT30M'));
                        }
                    }

                }
                $start_time->add(new \DateInterval("P1D"));
            }

            $days=1;
            $daystoadd = [];
            $max=0;
            ksort($calendar);
            foreach($calendar as $k=>&$day){
                $day->percentage = round($day->classes*0.25) - $day->busy;
                $days++;
                $max=$max<count($day->hours)?count($day->hours):$max;
            }

            foreach($daystoadd as $dayadd){
                $calendar[$dayadd] = new \stdClass();
                $calendar[$dayadd]->percentage=0;
                $calendar[$dayadd]->busy=0;
                $calendar[$dayadd]->classes=0;
                $calendar[$dayadd]->hours=[];
                $calendar[$dayadd]->ownhours=[];
            }
            ksort($calendar);
        }

        return view("admin.prebook.includes.calendar",["calendar"=>$calendar,"teacher"=>$teacher_id,"max"=>$max,"limit_prebook"=>$user->buy_prebooks->first(),"ownhours"=>$ownhours]);
    }

    public function getConfirmPrebook(Request $request){
        $user_id = $request->input('user_id');
        $selecteds = $request->input('selected');

        foreach($selecteds as &$selected){
            $selected=(explode(",",$selected));
            $selected[2]=User::where("id",$selected[2])->first();
        }

        return view("admin.prebook.confirm",["prebooks"=>$selecteds,"breadcrumb"=>true,"user_id"=>$user_id]);
    }

    public function savePrebook(Request $request){
        $user_id=$request->input('user_id');
        $user=User::where("id",$user_id)->first();
        $selecteds=$request->input("selected");
        $errors=[];
        $errors_classes=false;
        $classes=[];
        $teacher_id=false;

        foreach($selecteds as $k=>$selected){
            $selected=explode(",",$selected);

            $teacher = User::where("id",$selected[2])->first();

            if($teacher_id==false){
                $teacher_id=$selected[2];
                Prebook::where("user_id",$user->id)->where("teacher_id",$selected[2])->delete();
                $prebooks = Prebook::where("user_id",$user->id)->get();
                $userPrebook = $user->buy_prebooks()->first();
                if(count($selecteds) > ($userPrebook->hours*2)-count($prebooks)) {
                    Log::error("Limit Exceded on prebooks: ".$user->email. "selecteds: ".var_export($selecteds,true));
                    return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$userPrebook->id])->withErrors("Available limit exceeded, you only have ".(($userPrebook->hours*2)-count($prebooks))." hour(s) available.");
                }
            }

            $start_time = \DateTime::createFromFormat("U",strtotime('monday this week'),new \DateTimeZone("UTC"));
            $start_time->add(new \DateInterval("P".(intval($selected[0])-1)."D"));

            $prebookTimeUser=\DateTime::createFromFormat("Y-m-d h:iA",$start_time->format("Y-m-d")." ".$selected[1],new \DateTimeZone($user->timezone));
            $prebookTimeUser = Classes::fixTime($prebookTimeUser);
            $prebookTime=clone $prebookTimeUser;
            $prebookTime=$prebookTime->setTimezone(new \DateTimeZone("UTC"));

            $checkPrebookStudent=Prebook::where("user_id",$user->id)->where("day",$prebookTime->format("N"))->where("hour",$prebookTime->format("H:i:s"))->first();
            $checkPrebook=Prebook::where("teacher_id",$selected[2])->where("day",$prebookTime->format("N"))->where("hour",$prebookTime->format("H:i:s"))->first();

            if ($checkPrebookStudent && count($selecteds)>1) {
                $errors[]="Uh oh! Looks like you already have a class prebooked on ".$prebookTimeUser->format("l")." at ".$prebookTimeUser->format("h:ia").".";
            } elseif ($checkPrebookStudent && count($selecteds)==1){
                return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$userPrebook->id])->withErrors("Uh oh! Looks like you already have a class prebooked on ".$prebookTimeUser->format("l")." at ".$prebookTimeUser->format("h:ia").".");
            } elseif ($checkPrebook && count($selecteds)>1){
                $errors[]="A student has already chosen that schedule (".$prebookTimeUser->format("l")." at ".$prebookTimeUser->format("h:ia")."), please select another schedule!";
            } elseif ($checkPrebook && count($selecteds)==1){
                return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$userPrebook->id])->withErrors("A student has already chosen that schedule (".$prebookTimeUser->format("l")." at ".$prebookTimeUser->format("h:ia")."), please select another schedule!");
            } else {
                $prebook = new Prebook();
                $prebook->user_id = $user->id;
                $prebook->teacher_id = $selected[2];
                $prebook->day = $prebookTime->format("N");
                $prebook->hour = $prebookTime->format("H:i:s");
                $prebook->type = $teacher->isdeleteacher==1?"dele":"real";
                $prebook->save();
                //save classes
                $current_day=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"));
                while((($current_day->format("U")-gmdate("U")) / (60*60*24))<14){

                    if($current_day->format("N") == $prebook->day){
                        if($current_day->format("Y-m-d")." ".$prebook->hour>gmdate("Y-m-d H:i:s")){
                            $classes[]=[$current_day->format("Y-m-d")." ".$prebook->hour,$prebook->teacher_id];
                        }
                    }
                    $current_day->add(new \DateInterval("P1D"));
                }
            }
        }
        if(!empty($classes)){

            $insertedClass=[];
            $failedClasses=[];

            foreach($classes as $class){

                if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly") {
                    if ($user->credits == 0) {
                        Log::info("User without credits in prebook");
                        continue;
                    }
                    $user->credits--;
                    User::where("id", $user->id)->update(["credits" => $user->credits]);
                }

                $classTime=\DateTime::createFromFormat("Y-m-d H:i:s",$class[0],new \DateTimeZone("UTC"));
                $classTime = Classes::fixTime($classTime);
                $validClass=false;
                $teacher=User::where("id",$class[1])->first();

                //check if time is available for teacher
                $availableCalendars=mUserCalendar::where("from","<=",$classTime->format("H:i:s"))->where("day",$classTime->format("N"))->where("user_id",$teacher->id)->get();

                foreach($availableCalendars as $availableCalendar){

                    if(!$availableCalendar->user || !$availableCalendar->user->activated){
                        continue;
                    }

                    if($availableCalendar->from<$availableCalendar->till && $classTime->format("H:i:s")>$availableCalendar->till){
                        continue;
                    }

                    //check if class slot is empty
                    $class=Classes::where("class_time",$classTime->format("Y-m-d H:i:s"))->where("teacher_id",$availableCalendar->user_id)->first();

                    if($class){
                        continue;
                    }

                    $validClass=true;
                }

                if(!$validClass) {

                    //verify same next day with complete
                    if($classTime->format("N")-1==0){
                        $availableCalendars=UserCalendar::where("till",">=",$classTime->format("H:i:s"))->where("day",7)->where("user_id",$teacher->id)->get();
                    } else {
                        $availableCalendars=UserCalendar::where("till",">=",$classTime->format("H:i:s"))->where("day",$classTime->format("N")-1)->where("user_id",$teacher->id)->get();
                    }

                    foreach($availableCalendars as $availableCalendar){

                        if(!$availableCalendar->user || !$availableCalendar->user->activated){
                            continue;
                        }

                        if($availableCalendar->from<$availableCalendar->till){
                            continue;
                        }

                        //check if class slot is empty
                        $class=Classes::where("class_time",$classTime->format("Y-m-d H:i:s"))->where("teacher_id",$availableCalendar->user_id)->first();
                        if($class){
                            continue;
                        }

                        $validClass=true;
                    }
                }

                //check if time is available for student
                if($user->getCurrentRol()->name!="coordinator"){
                    $class=Classes::where("class_time",$classTime->format("Y-m-d H:i:s"))->where("user_id",$user->id)->first();
                    if($class){
                        $errors[]="Uh oh! Looks like you already have a class booked on ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("l d")." at ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("h:ia").".";
                        if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly") {
                            $user->credits++;
                            User::where("id", $user->id)->update(["credits" => $user->credits]);
                        }
                        continue;
                    }
                }

                if($validClass) {
                    //Class Ocupy
                    Log::info("Preebook: try to insert class for: ".$user->email);
                    if(!$this->insertClass($classTime,$teacher,$user->id)){
                        $failedClasses[]=[$classTime,$teacher];
                        $errors_classes=true;
                        $errors[]='While we’ve locked down all of your selected slots to be prebooked going forward, one or more of your selected times had already been booked by someone else for this week (as it was available for normal booking). Please double check your <a href="'.route("classes").'">Scheduled Classes</a> page.';
                        if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly") {
                            $user->credits++;
                            User::where("id", $user->id)->update(["credits" => $user->credits]);
                        }
                        Log::error("Preebook: error on insert class for ".$user->email. " classtime ". var_export($classTime,true) . "Teacher". var_export($teacher,true));
                    } else {
                        Log::info("Preebook: inserted class for ".$user->email. " classtime ". var_export($classTime,true) . "Teacher". var_export($teacher,true));
                        $insertedClass[]=[$classTime,$teacher];
                    }

                } else {
                    $errors_classes=true;
                    $errors[]='While we’ve locked down all of your selected slots to be prebooked going forward, one or more of your selected times had already been booked by someone else for this week (as it was available for normal booking). Please double check your <a href="'.route("classes").'">Scheduled Classes</a> page.';
                    if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly") {
                        $user->credits++;
                        User::where("id", $user->id)->update(["credits" => $user->credits]);
                    }
                }
            }


            $classes_for_student=[];
            foreach($insertedClass as $inserted){
                $class=Classes::where("teacher_id",$inserted[1]->id)->where("class_time",$inserted[0]->format("Y-m-d H:i:s"))->first();
                if(isset($classes_for_student[$class->zoom_id])){
                    continue;
                } else {
                    $classes_for_student[$class->zoom_id]=$class;
                }

            }

            try {
                if (\App::environment('production')) {
                    \Mail::send('emails.student_class_confirmed', ["user" => $user, "classes_for_student" => $classes_for_student], function ($message) use ($user) {
                        $message->subject(__('Class Confirmed'));
                        $message->to($user->email, $user->first_name);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Cant send email: '.$e->getMessage());
            }

            //just 1 for same teacher with same zoom id
            $classes_for_teachers=[];
            foreach($classes_for_student as $class_for_student){
                if(isset($classes_for_teachers[$class_for_student->teacher->id])){
                    $classes_for_teachers[$class_for_student->teacher->id][]=$class_for_student;
                } else {
                    $classes_for_teachers[$class_for_student->teacher->id]=[$class_for_student];
                }
            }

            $unknown_teachers=[];
            foreach($classes_for_teachers as $classes_for_teacher){
                $teacher=$classes_for_teacher[0]->teacher;
                try {
                    if (\App::environment('production')) {
                        \Mail::send('emails.teacher_class_confirmed', ["user" => $user, "teacher" => $teacher, "classes_for_teacher" => $classes_for_teachers], function ($message) use ($teacher) {
                            $message->subject(__('Class Confirmed'));
                            $message->to($teacher->email, $teacher->first_name);
                        });
                    }
                } catch (\Exception $e) {
                    Log::error('Cant send email: '.$e->getMessage());
                }

                $know_student=Classes::where("user_id",$user->id)->where("teacher_id",$teacher->id)->where("zoom_invitation",1)->count();

                if(!$know_student){
                    try {
                        if (\App::environment('production')) {
                            \Mail::send('emails.teacher_zoom_invitation', ["user" => $user, "teacher" => $teacher], function ($message) use ($teacher) {
                                $message->subject(__("New Student with Zoom"));
                                $message->to($teacher->email, $teacher->first_name);
                            });
                        }
                    } catch (\Exception $e) {
                        Log::error('Cant send email: '.$e->getMessage());
                    }
                    Classes::where("user_id",$user->id)->where("teacher_id",$teacher->id)->update(["zoom_invitation"=>1]);

                    foreach ($classes_for_teacher as $class){

                        $limit_time=\DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->sub(new \DateInterval("PT2H"));
                        $limit_time = Classes::fixTime($limit_time);
                        if($limit_time->format("U")<gmdate("U")){
                            $unknown_teachers[]=$teacher;
                            break;
                        }
                    }
                }
            }
        }

        if($errors_classes) {
            try {
                if (\App::environment('production')) {
                    \Mail::send('emails.student_class_prebook_error', ["user" => $user], function ($message) use ($user) {
                        $message->subject("BaseLang class error");
                        $message->to($user->email, $user->first_name);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Cant send email: '.$e->getMessage());
            }
        }

        if(!empty($errors)){
            return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$userPrebook->id])->withErrors($errors);
        }

        return redirect()->route("admin_prebooks_edit",["buy_prebook_id"=>$userPrebook->id]);
    }


    private function insertClass($time,$teacher,$user_id){
        $user = User::where("id",$user_id)->first();

        $current_subscription = $user->getCurrentSubscriptionType();
        if($current_subscription && ($current_subscription == "dele_real" || $current_subscription == "dele")){
            $subscriptionType = "dele";
        }
        elseif($current_subscription){
            $subscriptionType = "real";
        }

        $class = new Classes();
        $class->user_id=$user->id;
        $class->teacher_id=$teacher->id;
        $class->class_time=$time->format("Y-m-d H:i:s");
        $class->type=$subscriptionType;
        $class->save();
        Log::info("Save class: ".$class->id." - teacher_id: ".$class->teacher_id." - user_id: ".$class->user_id);
        return $class->createZoom($teacher);
    }

    public function getAvailabilityTeachers(){
        return view("admin.prebook.availability_of_teachers",["breadcrumb"=>true]);
    }

    public function getCheckAvailability($day){
        $calendars = UserCalendar::where("day",$day)->get();
        $start_time = \DateTime::createFromFormat("U",strtotime('monday this week'),new \DateTimeZone("UTC"));

        while($start_time->format("N") < $day) {
            $start_time->add(new \DateInterval("P1D"));
        }

        $teachers = [];

        foreach($calendars as $calendar) {

            $teacher = $calendar->user;

            if($teacher && $teacher->activated) {

                $teacher_prebooks = Prebook::where("teacher_id",$teacher->id)->get();

                if(!isset($teachers[$teacher->id])){
                    $teachers[$teacher->id] = new \stdClass();
                    $teachers[$teacher->id]->id = $teacher->id;
                    $teachers[$teacher->id]->first_name = $teacher->first_name;
                    $teachers[$teacher->id]->zoom_email = $teacher->zoom_email;
                    $teachers[$teacher->id]->hours = 0;
                    $teachers[$teacher->id]->busy = 0;
                    $teachers[$teacher->id]->percentage = 0;
                }

                $from = \DateTime::createFromFormat("Y-m-d H:i:s",$start_time->format("Y-m-d").' '.$calendar->from)->setTimezone(new \DateTimeZone("America/Caracas"));
                $till = \DateTime::createFromFormat("Y-m-d H:i:s",$start_time->format("Y-m-d").' '.$calendar->till)->setTimezone(new \DateTimeZone("America/Caracas"));

                if($till<$from){
                    $till->add(new \DateInterval("P1D"));
                }

                $new_from = clone $from;
                $new_till = clone $till;

                while($new_from < $new_till) {
                    $time = clone $new_from;
                    $time = $time->setTimezone(new \DateTimeZone("UTC"));
                    $checkPrebook = $teacher_prebooks->where("day",$time->format("N"))->where("hour",$time->format("H:i:s"))->first();

                    if($checkPrebook) {
                        $teachers[$teacher->id]->busy++;
                    }

                    $new_from->add(new \DateInterval('PT30M'));
                }

                $from = new \DateTime($from->format("Y-m-d H:i:s"));
                $till = new \DateTime($till->format("Y-m-d H:i:s"));
                $diff = $from->diff($till);
                $diff = $diff->h;

                $teachers[$teacher->id]->hours+=$diff;
                $teachers[$teacher->id]->busy/=2;
                $teachers[$teacher->id]->percentage = round($teachers[$teacher->id]->hours*0.25) - $teachers[$teacher->id]->busy;

            }
        }

        $col = "first_name";
        $array_aux = array();

        foreach($teachers as $key => $row) {
            $array_aux[$key] = is_object($row) ? $array_aux[$key] = $row->$col : $row[$col];
            $array_aux[$key] = strtolower($array_aux[$key]);
        }

        array_multisort($array_aux, SORT_ASC, $teachers);

        return view("admin.prebook.includes.teachers",["breadcrumb"=>true, "teachers"=>$teachers]);
    }
}
