<?php

namespace App\Http\Controllers;

use App\Models\BlockDay;
use App\Http\Helper\Calendar;
use App\Http\Helper\CalendarEvent;
use App\Models\BuyInmersion;
use App\Models\Classes;
use App\Models\Location;
use App\Models\Role;
use App\Models\UserCalendar;
use App\Models\UserCredits;
use App\Models\UserEvaluation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use DB;
use Session;
use Modules\Classes\Entities\Zoom;

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

    private function removeClass($class){
        $user = User::getCurrent();

        $class->removeZoom();
        $class->delete();

        try {
            if (\App::environment('production')) {
                \Mail::send('emails.student_class_cancelled', ["user" => $user, "class" => $class], function ($message) use ($user) {
                    $message->subject("BaseLang class cancelled");
                    $message->to($user->email, $user->first_name);
                });


                $teacher = $class->teacher;
                \Mail::send('emails.teacher_class_cancelled', ["teacher" => $teacher, "user" => $user, "class" => $class], function ($message) use ($teacher) {
                    $message->subject("BaseLang class cancelled");
                    $message->to($teacher->email, $teacher->first_name);
                });
            }
        } catch (\Exception $e) {
            Log::error('Cant send email: '.$e->getMessage());
        }

        return true;
    }

    private function insertClass($time,$teacher,$location_id){
        $user = User::getCurrent();

        $subscriptionType=session("current_subscription");

        if(isset($location_id)) {
            $verify_inmersion = false;

            $inmersion = BuyInmersion::where("teacher_id",$teacher->id)->where("inmersion_start","<=",$time->format("Y-m-d"))->where("inmersion_end",">=",$time->format("Y-m-d"))->first();

            $location = Location::find($location_id);

            if($inmersion && $location) {
                $time_check = clone $time;
                $time_check = $time_check->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");

                if($subscriptionType=="real") {
                    if($inmersion->hour_format=="AM" && $time_check>="08:30:00" && $time_check<="12:30:00") {
                        $verify_inmersion = true;
                    }

                    if($inmersion->hour_format=="PM" && $time_check>="13:30:00" && $time_check<="17:30:00") {
                        $verify_inmersion = true;
                    }
                }elseif($subscriptionType=="dele") {
                    if($inmersion->hour_format=="AM" && $time_check>="08:00:00" && $time_check<="12:30:00") {
                        $verify_inmersion = true;
                    }

                    if($inmersion->hour_format=="PM" && $time_check>="13:00:00" && $time_check<="17:30:00") {
                        $verify_inmersion = true;
                    }
                }
            }

            if($verify_inmersion) {
                return false;
            }
        }

        try {
            
            
            $class                  = new Classes();
           
            $class->user_id         =$user->id;
            $class->teacher_id      =$teacher->id;
            $class->class_time      =$time->format("Y-m-d H:i:s");
            $class->type            =$subscriptionType;
            $class->location_id     =$location_id;
            $zoom_link              = Zoom::createAMeeting($class);
            //Log::info(var_export($zoom_link,true));
            if(isset($zoom_link->join_url)) {
                $class->session_link    = $zoom_link->join_url;
            }
            
            // dd($class);
            $class->save();
            

            Log::info("Save class: ".$class->id." - teacher_id: ".$class->teacher_id." - user_id: ".$class->user_id);
        } catch (\Exception $e){
            //dd($e->getMessage());
            Log::error($e);
            Log::info("Error Savina Class: ".var_export($time,true)." - teacher_id: ".var_export($teacher,true)." - user_id: ".var_export($location_id,true));
            return false;
        }

        return $class->createZoom($teacher);
    }

    public function getBookedClass(){
        $unknown_teachers=false;
        $teachers=false;

        if(session("unknown_teachers")){
            $unknown_teachers=session("unknown_teachers");
        }

        $menu_active = false;
        if(session("menu_active")){
            $menu_active=session("menu_active");
        }

        if($unknown_teachers && !empty($unknown_teachers)){

            $teachers=new \stdClass();
            $teachers->teacher="";
            $teachers->teacher_email="";
            $teachers->single=(count($unknown_teachers)==1?true:false);

            foreach($unknown_teachers as $k=>$unknown_teacher){

                if(count($unknown_teachers)==$k+1 && $k!=0){
                    $teachers->teacher.="and ";
                    $teachers->teacher_email.="and ";
                }

                $teachers->teacher.=$unknown_teacher->first_name.", ";
                $teachers->teacher_email.=$unknown_teacher->email;
                if(count($unknown_teachers)!=$k+1) {
                    $teachers->teacher_email .= ", ";
                }
            }

        }

        return view("calendar.booked",["menu_active"=>$menu_active,"breadcrumb"=>true,"unknown_teachers"=>$teachers]);
    }

    public function getTeacherHistory($page=1){
        return view("calendar.teacher_history",["menu_active"=>"history_classes","page"=>$page]);
    }

    public function getTeacherHistoryClasses($skip=0,$pages=1){
        $user = User::getCurrent();
        $take=10*$pages;
        $showMore=false;

        $classes=Classes::where("class_time","<=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","desc")->where("teacher_id",$user->id)->skip($skip)->take($take)->get();
        $firstClass=Classes::where("class_time","<=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("teacher_id",$user->id)->first();
        $firstInClasses=$classes->last();

        if($firstClass){
            if($firstClass->id!=$firstInClasses->id){
                $showMore=true;
            }
        }

        return view("calendar.includes.teacher_history_list",["classes"=>$classes,"showMore"=>$showMore]);

    }

    public function getHistory($page=1){
        return view("calendar.history",["menu_active"=>"history_classes","page"=>$page]);
    }

    public function getHistoryClasses($skip=0,$pages=1){
        $user = User::getCurrent();
        $take=10*$pages;
        $subscriptionType=session("current_subscription");
        $showMore=false;

        $classes=Classes::where("class_time","<=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","desc")->where("type",$subscriptionType)->where("user_id",$user->id)->skip($skip)->take($take)->get();
        $firstClass=Classes::where("class_time","<=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("type",$subscriptionType)->where("user_id",$user->id)->first();
        $firstInClasses=$classes->last();

        if($firstClass){
            if($firstClass->id!=$firstInClasses->id){
                $showMore=true;
            }
        }

        foreach($classes as $class) {
            if(isset($class->location_id)) {
                $location = Location::find($class->location_id);
                if($location) {
                    $class->location = ucwords(strtolower($location->name));
                }else {
                    $class->location = "undefined";
                }
            }else {
                $class->location = "online";
            }
        }

        return view("calendar.includes.history_list",["classes"=>$classes,"showMore"=>$showMore]);

    }

    public function getICS(){
        $user = User::getCurrent();

        $calendar = new Calendar(
            array('title'=>'BaseLang Calendar Classes','author'=>'BaseLang.com')
        );

        $classes =  $user->classes()->where('class_time','>=',gmdate("Y-m-d H:i:s"))->orderBy('class_time','asc')->get();


        $events = array();

        $index=0;
        $current_teacher=false;
        $class_collect_time=false;
        foreach($classes as $k=>$class) {
            $class->classes_count=1;
            if(!$current_teacher){
                $current_teacher=$class->teacher_id;
                $class_collect_time=\DateTime::createFromFormat('Y-m-d H:i:s', $class->class_time);
                $index=$k;
                continue;
            }

            $class_collect_time->modify('+30 minutes');


            if($current_teacher==$class->teacher_id && $class->class_time==$class_collect_time->format("Y-m-d H:i:s")){
                $classes[$index]->classes_count++;
                $classes[$k]=false;
            } else {
                $class_collect_time=\DateTime::createFromFormat('Y-m-d H:i:s', $class->class_time);
                $index=$k;
                $current_teacher=$class->teacher_id;
            }

        }

        foreach($classes as $class) {
            if(!$class){
                continue;
            }
            $classdatetime = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time);
            $classdatetimeend = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time);


            for($i=1;$i<=$class->classes_count;$i++){

                $classdatetimeend->modify('+30 minutes');

                if($class->type=="dele"){
                    $classdatetimeend->modify('+30 minutes');
                };
            }


            $event_parameters = array(
                'uid' =>  $class->id,
                'summary' => 'BaseLang Class with '.$class->teacher->first_name,
                'description' => 'Your class is with Zoom!',
                'location'=>"Zoom",
                'start' => $classdatetime,
                'end' => $classdatetimeend
            );

            $event = new CalendarEvent($event_parameters);
            $events[] = $event;


        }

        $calendar->events = $events;
        return $calendar->show();
    }

    public function getTeacherClasses($student=null){
        //$user = User::getCurrent();
        //$classes=Classes::where("class_time",">=",gmdate("Y-m-d")." 00:00:00")->orderBy("class_time","asc")->where("teacher_id",$user->id)->get();
        return view("calendar.teacher_scheduled",["menu_active"=>"classes","student"=>$student]);
    }

    public function loadTeacherClasses($student=null){
        $user = User::getCurrent();
        $classes = Classes::where("class_time",">=",gmdate("Y-m-d")." 00:00:00")->orderBy("class_time","asc")->where("teacher_id",$user->id)->get();
        return view("calendar.includes.teacher_classes",["classes"=>$classes,"student"=>$student]);
    }

    public function getClasses(){
        $user = User::getCurrent();
        $subscriptionType=session("current_subscription");
        $limitTime = \DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"))->modify('-15 minutes');
        $classes=Classes::where("class_time",">=",$limitTime->format("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("type",$subscriptionType)->where("user_id",$user->id)->get();
        foreach($classes as $class) {
            if(isset($class->location_id)) {
                $location = Location::find($class->location_id);
                if($location) {
                    $class->location = ucwords(strtolower($location->name));
                }else {
                    $class->location = "undefined";
                }
            }else {
                $class->location = "online";
            }
        }

        return view("calendar.scheduled",["menu_active"=>"classes","classes"=>$classes]);

    }

    public function cancelClass(Request $request){
        $user = User::getCurrent();
        $class=$request->input("class");

        $class=Classes::where("id",$class)->where("user_id",$user->id)->first();
        if(!$class){
            return redirect()->route("classes")->withErrors(["Class is already cancelled"]);
        }

        if(!$this->removeClass($class)){
            return redirect()->route("classes")->withErrors(["An error has occurred trying to process your cancellation. "]);
        };

        if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly"){
            $user->credits++;
            Log::info("Credit recover by cancel class: ".$user->email." credits:".$user->credits);
            User::where("id",$user->id)->update(["credits"=>$user->credits]);
        }

        return redirect()->route("classes")->with(["message_info"=>"{{ __('Your class has been cancelled') }}"]);
    }

    public function saveClasses(Request $request){
        //Local Datetime
        $user = User::getCurrent();

        $selecteds = $request->input("selected");
        $location_id = $request->input('location_id');

        $menu_active = "classes_new";
        if(isset($location_id)) {
            $menu_active = "classes_in_person_new";
        }

        $insertedClass=[];
        $failedClasses=[];
        $errors=[];

        if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly"){
            //check if credits enough or try to buy
            if($user->credits>=count($selecteds)){
                $user->credits-=count($selecteds);
                Log::info("Credit less by book class: ".$user->email." credits:".$user->credits." classes count:".count($selecteds));
                User::where("id",$user->id)->update(["credits"=>$user->credits]);
            } else {
                //buy credits
                $credits=(count($selecteds)-$user->credits);
                $credit_price=UserCredits::getCreditsPrice($credits);

                $total=($credit_price*$credits/2);

                Log::info("Buy Credits: ".$credits." user credits:".$user->credits." classes count:".count($selecteds));

                try {
                    $result = \ChargeBee_Invoice::charge([
                        "customerId" => $user->chargebee_id,
                        "amount" => ($total*100),
                        "description" => "BaseLang Class Credits"
                    ]);

                    $invoice = $result->invoice();
                    $user->credits=0;
                    User::where("id",$user->id)->update(["credits"=>0]);
                    Log::info("Saving credits to 0, buy on booking: ".$user->email);
                    UserCredits::create(["credits"=>$credits,"user_id"=>$user->id,"billing_cycle"=>0,"subscription_id"=>$user->getCurrentSubscription()->subscription_id]);
                } catch (\Exception $e){
                    Log::error("Error When Buy Credits: ".$e->getMessage());

                    if(isset($result)){
                        Log::error("Error When Buy Credits: ".var_export($result,true));
                    }

                    if(isset($location_id)) {
                        return redirect()->route("classes_in_person_new")->withErrors(["Your payment method rejected the charge. Please try again, contact your bank or <a href='".route("change_card")."'>Click here to change your payment method</a>"]);
                    }else {
                        return redirect()->route("classes_new")->withErrors(["Your payment method rejected the charge. Please try again, contact your bank or <a href='".route("change_card")."'>Click here to change your payment method</a>"]);
                    }

                }

            };


        }

        foreach($selecteds as $selected){
            $selected=explode(",",$selected);
            $classTime=\DateTime::createFromFormat("Y-m-d H:i:s",$selected[0],new \DateTimeZone($user->timezone));
            $classTime = Classes::fixTime($classTime);

            $classTimeOffset=\DateTime::createFromFormat("Y-m-d H:i:s",$selected[0],new \DateTimeZone($user->timezone))->sub(new \DateInterval("PT24H"));

            if($classTimeOffset->getOffset()!=$classTime->getOffset()) {
                $offsetInterval = $classTimeOffset->getOffset() - $classTime->getOffset();

                if($offsetInterval>0){
                    $classTimeOffset = \DateTime::createFromFormat("Y-m-d H:i:s",$selected[0])->sub(new \DateInterval("PT".$offsetInterval."S"));
                } else {
                    $offsetInterval*=-1;
                    $classTimeOffset = \DateTime::createFromFormat("Y-m-d H:i:s",$selected[0])->add(new \DateInterval("PT".$offsetInterval."S"));
                }
                $classTimeOffset->setTimezone(new \DateTimeZone($user->timezone));
                $classTimeOffset = Classes::fixTime($classTimeOffset);

                if($classTime->format("Y-m-d H:i:s")==$classTimeOffset->format("Y-m-d H:i:s")){
                    $classTime = clone $classTimeOffset;
                };

            }


            $classTime->setTimezone(new \DateTimeZone("UTC"));

            $validClass=false;
            $teacher=User::where("id",$selected[1])->first();
            //check if time is available for teacher

            $availableCalendars=UserCalendar::where("from","<=",$classTime->format("H:i:s"))->where("day",$classTime->format("N"))->where("user_id",$teacher->id)->get();

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
            };

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
                    };

                    //check if class slot is empty
                    $class=Classes::where("class_time",$classTime->format("Y-m-d H:i:s"))->where("teacher_id",$availableCalendar->user_id)->first();
                    if($class){
                        continue;
                    }

                    $validClass=true;
                };
            }

            //check if time is available for student
            if($user->getCurrentRol()->name!="coordinator"){
                $class=Classes::where("class_time",$classTime->format("Y-m-d H:i:s"))->where("user_id",$user->id)->first();
                if($class){
                    $errors[]="Uh oh! Looks like you already have a class booked on ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("l d")." at ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("h:ia").".";
                    if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly") {
                        $user->credits++;
                        User::where("id", $user->id)->update(["credits" => $user->credits]);
                        Log::info("Credit plus by booked class: " . $user->email . " credits:" . $user->credits . " classes:" . $class->id);
                    }
                    continue;
                }
            }


            if($validClass) {
                //Class Ocupy
                if(!$this->insertClass($classTime,$teacher,$location_id)){
                    $failedClasses[]=[$classTime,$teacher];
                    //Uh oh! Looks like you already have a class booked at one or more of the selected times.
                    //Some of
                    $errors[]="Your selected class with ".$teacher->first_name." on ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("l d")." at ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("h:ia")." is not available anymore (It's likely someone else is booking the same time right now).";
                    if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly") {
                        $user->credits++;
                        User::where("id", $user->id)->update(["credits" => $user->credits]);
                        Log::info("Credit plus by booked class: " . $user->email . " credits:" . $user->credits . " classes:" . $class->id);
                    }
                } else {
                    $insertedClass[]=[$classTime,$teacher];
                };

            } else {
                $errors[]="Your selected class with ".$teacher->first_name." on ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("l d")." at ".$classTime->setTimezone(new \DateTimeZone($user->timezone))->format("h:ia")." is not available anymore (It's likely someone else is booking the same time right now).";
                if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly") {
                    $user->credits++;
                    User::where("id", $user->id)->update(["credits" => $user->credits]);
                    Log::info("Credit plus by booked class: " . $user->email . " credits:" . $user->credits . " classes:" . $teacher->first_name . " at " . var_export($classTime, true));
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

        };

        try {
            
                \Mail::send('emails.student_class_confirmed', ["user" => $user, "classes_for_student" => $classes_for_student], function ($message) use ($user) {
                    $message->subject(__('Class Confirmed'));
                    $message->to($user->email, $user->first_name);
                });

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
        };

        $unknown_teachers=[];
        foreach($classes_for_teachers as $classes_for_teacher){
            $teacher=$classes_for_teacher[0]->teacher;
            if(\App::environment('production')){
                try {
                    \Mail::send('emails.teacher_class_confirmed', ["user" => $user, "teacher" => $teacher, "classes_for_teacher" => $classes_for_teachers], function ($message) use ($teacher) {
                        $message->subject(__('Class Confirmed'));
                        $message->to($teacher->email, $teacher->first_name);
                    });
                } catch (\Exception $e) {
                    Log::error('Cant send email: '.$e->getMessage());
                }
            }

            $know_student=Classes::where("user_id",$user->id)->where("teacher_id",$teacher->id)->where("zoom_invitation",1)->count();

            if(!$know_student){
                if(\App::environment('production')){
                    try {
                        \Mail::send('emails.teacher_zoom_invitation', ["user" => $user, "teacher" => $teacher], function ($message) use ($teacher) {
                            $message->subject(__("New Student with Zoom"));
                            $message->to($teacher->email, $teacher->first_name);
                        });
                    } catch (\Exception $e) {
                        Log::error('Cant send email: '.$e->getMessage());
                    }
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

        if(!empty($errors) && empty($insertedClass)){

            if(isset($location_id)) {
                return redirect()->route("classes_in_person_new")->withErrors($errors);
            }else {
                return redirect()->route("classes_new")->withErrors($errors);
            }

        } elseif(!empty($errors)){
            return redirect()->route("booked_classes")->withErrors($errors)->with(["unknown_teachers"=>$unknown_teachers, "menu_active"=>$menu_active]);
        }

        return redirect()->route("booked_classes")->with(["unknown_teachers"=>$unknown_teachers, "menu_active"=>$menu_active]);
    }


    public function getConfirmClasses(Request $request){
        $user = User::getCurrent();
        //Local Datetime
        $selecteds = $request->input('selected');
        $location_id = $request->input('location_id');

        $menu_active = "classes_new";
        if(isset($location_id)) {
            $menu_active = "classes_in_person_new";
        }

        try {
            foreach($selecteds as &$selected){
                $selected=(explode(",",$selected));

                if(empty($user->timezone)){
                    $user->timezone="UTC";
                    User::where("id",$user->id)->update(["timezone"=>"UTC"]);
                }

                $classDateTime=\DateTime::createFromFormat("Y-m-d H:i:s",$selected[0],new \DateTimeZone($user->timezone));
                $classDateTime = Classes::fixTime($classDateTime);
                if(!$classDateTime){
                    Log::error("Error With TimeZone: ".var_export($selected[0],true)." User: ".$user->timezone);
                }

                $selected[0]=$classDateTime;
                $selected[1]=User::where("id",$selected[1])->first();
            };
        } catch (\Exception $e){
            Log::error('Error on Confirm Classes: '.$e->getMessage().$e->getLine());
            Log::error('Error on Confirm Classes: '.var_export($selecteds,true).' For: '.$user->email);
            return redirect()->route('classes_new')->withErrors('Error with selected classes, Please try again.');
        }

        return view("calendar.confirm",["menu_active"=>$menu_active,"classes"=>$selecteds,"breadcrumb"=>true,"location_id"=>$location_id]);
    }

    public function getChooseTeacher(Request $request){
        $user = User::getCurrent();
        $subscriptionType=session("current_subscription")=="real"?"real":"dele";
        $subscription_plan = Session("subscription_plan");
        $in_persson=false;
        $extra_dele = false;
        //Local Datetime
        $selecteds = $request->input('selected');
        $location_id = $request->input('location_id');
        
        $teachers = null;
        $location = null;
        $menu_active = "classes_new";
        if(isset($location_id)) {
            $teachers = collect();
            $verify_teachers = Role::where('name', 'teacher')->first()->users()->where("activated", 1)->orderBy("first_name","ASC")->get();
            foreach($verify_teachers as $teacher) {
                if($teacher->hasLocation($location_id)) {
                    $teachers->push($teacher);
                }
            }
            $menu_active = "classes_in_person_new";
            $location = Location::find($location_id);
            Log::info("User with location: ".$location_id);
        }

        if($menu_active == "classes_in_person_new" && $subscription_plan == "medellin_rw_lite")
        {
            $selecteds_mapping = array();
            for($i=0; $i<count($selecteds); $i++){
                $sel_date = explode(" ",$selecteds[$i]);
                if(array_key_exists($sel_date[0], $selecteds_mapping)){
                    $selecteds_mapping[$sel_date[0]] = $selecteds_mapping[$sel_date[0]]+1;
                    if($selecteds_mapping[$sel_date[0]] > 4)
                    {
                     return redirect()->route('classes_in_person_new')->withErrors('You can only book 4 slots or book two hours of in-person classes in a day, which is the limited with the Lite plan.');
                    }
                }
                else {
                    $selecteds_mapping[$sel_date[0]] = 1;
                }
            }

            $user_booked_data = Classes::where("user_id",$user->id)->where("location_id",1)->orderBy("class_time","desc")->get();

            $pre_bookclass_mapping = array();
            if($user_booked_data){
                foreach($user_booked_data as $user_prebook){
                    $prebook_sel_date = explode(" ",$user_prebook->class_time);
                    if(array_key_exists($prebook_sel_date[0], $pre_bookclass_mapping)){
                        $pre_bookclass_mapping[$prebook_sel_date[0]] = $pre_bookclass_mapping[$prebook_sel_date[0]]+1;
                    }
                    else {
                        $pre_bookclass_mapping[$prebook_sel_date[0]] = 1;
                    }
                }
			}

            $selecteds_mapping_dates = array_keys($selecteds_mapping);
            for($i=0; $i<count($selecteds_mapping_dates); $i++){
                if(array_key_exists($selecteds_mapping_dates[$i], $pre_bookclass_mapping)){
                    $prebook_count = $pre_bookclass_mapping[$selecteds_mapping_dates[$i]];
                    $selected_count = $selecteds_mapping[$selecteds_mapping_dates[$i]];
                    if(($prebook_count + $selected_count) > 4)
                    {
                        return redirect()->route('classes_in_person_new')->withErrors('You can only book 4 slots or book two hours of in-person classes in a day, which is the limited with the Lite plan.');
                    }
                }
            }
        }

        if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule){
            if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                $in_persson=true;
                $extra_dele=true;
                if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite"])){
                    $subscriptionType = "real";
                }else{
                    $subscriptionType = "dele";
                }
            }
        }

        if(!$in_persson && $user->location_id){
            $in_persson=true;
        }

        Log::info('User: '.$user->email.' with in persson: '.($in_persson?'Yes':'No'));
        Log::info('User: '.$user->email.' with subscription type: '.$subscriptionType);


        if(!isset($selecteds)){
            Log::error('Error on Choose teacher Classes: '.var_export($request->all(),true). ' FOR: '.$user->email);
            if(isset($location_id)) {
                return redirect()->route('classes_in_person_new')->withErrors('Error with selected classes, Please try again.');
            }else {
                return redirect()->route('classes_new')->withErrors('Error with selected classes, Please try again.');
            }
        };
        $classes=[];
        foreach($selecteds as &$selected){
            $selected=(explode(" ",$selected));

            $selected=\DateTime::createFromFormat("Y-m-d H:i",$selected[0]." ".$selected[1],new \DateTimeZone($user->timezone));

            $selected->setTimezone(new \DateTimeZone("UTC"));

            $selected = Classes::fixTime($selected);
            $availableCalendars=UserCalendar::where("from","<=",$selected->format("H:i:s"))->where("day",$selected->format("N"))->get();

            $teachers_id = [];
            if(isset($location_id) && $teachers) {
                foreach($availableCalendars as $key => $availableCalendar){
                    foreach($teachers as $teacher){
                        if($availableCalendar->user_id==$teacher->id){
                            $teachers_id[] = $teacher->id;
                        }
                    }
                }

                $availableCalendars = $availableCalendars->whereIn("user_id",$teachers_id);
            }

            foreach($availableCalendars as $availableCalendar){
                $teacher=false;

                if($availableCalendar->user && !$availableCalendar->user->hasRole("teacher")){
                    UserCalendar::where("user_id",$availableCalendar->user_id)->delete();
                    continue;
                }

                if(!$availableCalendar->user || !$availableCalendar->user->activated){
                    continue;
                }

                if(!isset($location_id) && $availableCalendar->user && $availableCalendar->user->block_online){
                    continue;
                }

                if($in_persson){
                    $teacher=$availableCalendar->user;
                    if($teacher->is_deleteacher){
                        if($selected->format('i')=='30'){
                            $check_dele_date = clone $selected;
                            $check_dele_date->setTimezone(new \DateTimeZone($user->timezone));
                            if($check_dele_date->getOffset()/3600 - floor($check_dele_date->getOffset()/3600)==0){
                                $teacher = false;
                            };
                        } else {
                            $check_dele_date = clone $selected;
                            $check_dele_date->setTimezone(new \DateTimeZone($user->timezone));
                            if($check_dele_date->getOffset()/3600 - floor($check_dele_date->getOffset()/3600)!=0){
                                $teacher = false;
                            };
                        }
                    }
                } elseif($subscriptionType=="real"){
                    if($availableCalendar->user->is_deleteacher==0){
                        $teacher=$availableCalendar->user;
                    }
                } else {
                    if($availableCalendar->user->is_deleteacher==1){
                        $teacher=$availableCalendar->user;
                    }
                }

                if(!$teacher){
                    continue;
                }

                if($availableCalendar->from<$availableCalendar->till && $selected->format("H:i:s")>=$availableCalendar->till){
                    continue;
                }
                //Log::info('Continue: '.var_export($availableCalendar->till,true));

                $class=$availableCalendar->user->teacher_classes()->where("class_time",$selected->format("Y-m-d H:i:s"))->first();
                $verify_inmersion = false;

                if($location) {
                    $localTime = \DateTime::createFromFormat("Y-m-d H:i:s",$selected->format("Y-m-d H:i:s"))->setTimezone(new \DateTimeZone($location->timezone));
                    $inmersion = BuyInmersion::where("teacher_id",$availableCalendar->user_id)->where("inmersion_start","<=",$localTime->format("Y-m-d"))->where("inmersion_end",">=",$localTime->format("Y-m-d"))->first();

                    if($inmersion) {
                        $localTime = $localTime->format("H:i:s");

                        if($subscriptionType=="real") {
                            if($inmersion->hour_format=="AM" && $localTime>="08:30:00" && $localTime<="12:30:00") {
                                $verify_inmersion = true;
                            }

                            if($inmersion->hour_format=="PM" && $localTime>="13:30:00" && $localTime<="17:30:00") {
                                $verify_inmersion = true;
                            }
                        }elseif($subscriptionType=="dele") {
                            if($inmersion->hour_format=="AM" && $localTime>="08:00:00" && $localTime<="12:30:00") {
                                $verify_inmersion = true;
                            }

                            if($inmersion->hour_format=="PM" && $localTime>="13:00:00" && $localTime<="17:30:00") {
                                $verify_inmersion = true;
                            }
                        }
                    }
                }

                $blocked_time = clone $selected;
                $blocked_time->setTimezone(new \DateTimeZone($availableCalendar->user->timezone));
                $not_available=false;

                $blocked_day = BlockDay::where("teacher_id",$availableCalendar->user_id)->where("blocking_day",$blocked_time->format("Y-m-d"))->first();

                if($blocked_day && isset($blocked_day->from) && isset($blocked_day->till)) {

                    $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                    $time_from->setTimezone(new \DateTimeZone($availableCalendar->user->timezone));
                    //Log::info($time_from->format("h:i:sa"));

                    $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                    $time_till->setTimezone(new \DateTimeZone($availableCalendar->user->timezone));
                    //Log::info($time_till->format("h:i:sa"));

                    if($blocked_time->format("H:i:s") >= $time_from->format("H:i:s") && $blocked_time->format("H:i:s") <= $time_till->format("H:i:s")) {
                        //Log::info($time_from->format("H:i:sa")." ".$blocked_time->format("H:i:sa")." ".$time_till->format("H:i:sa"));
                        $not_available = true;
                    }

                }elseif($blocked_day && !isset($blocked_day->from) && !isset($blocked_day->till)) {
                    $not_available = true;
                }


                if($class || $verify_inmersion || $not_available){
                    continue;
                }

                if(!isset($classes[$selected->format("Y-m-d H:i:s")])){
                    $classes[$selected->format("Y-m-d H:i:s")]=[$availableCalendar->user_id];
                } else {
                    $classes[$selected->format("Y-m-d H:i:s")][]=$availableCalendar->user_id;
                }
            };


            if($selected->format("N")-1==0){
                $availableCalendars=UserCalendar::where("till",">",$selected->format("H:i:s"))->where("day",7)->get();
            } else {
                $availableCalendars=UserCalendar::where("till",">",$selected->format("H:i:s"))->where("day",$selected->format("N")-1)->get();
            }

            $teachers_id = [];
            if(isset($location_id) && $teachers) {
                foreach($availableCalendars as $key => $availableCalendar){
                    foreach($teachers as $teacher){
                        if($availableCalendar->user_id==$teacher->id){
                            $teachers_id[] = $teacher->id;
                        }
                    }
                }

                $availableCalendars = $availableCalendars->whereIn("user_id",$teachers_id);
            }

            foreach($availableCalendars as $availableCalendar){

                if($in_persson){
                    $teacher=User::where('id', $availableCalendar->user_id)->first();
                } elseif($subscriptionType=="real"){
                    $teacher=User::where('id', $availableCalendar->user_id)->where('is_deleteacher', 0)->first();
                } else {
                    $teacher=User::where('id', $availableCalendar->user_id)->where('is_deleteacher', 1)->first();
                }

                if(!$teacher){
                    continue;
                }

                if(!$availableCalendar->user || !$availableCalendar->user->activated){
                    continue;
                }

                if(!isset($location_id) && $availableCalendar->user && $availableCalendar->user->block_online){
                    continue;
                }

                if($availableCalendar->from<$availableCalendar->till){
                    continue;
                };

                $class=Classes::where("class_time",$selected->format("Y-m-d H:i:s"))->where("teacher_id",$availableCalendar->user_id)->first();

                $verify_inmersion = false;

                if($location) {
                    $localTime = \DateTime::createFromFormat("Y-m-d H:i:s",$selected->format("Y-m-d H:i:s"))->setTimezone(new \DateTimeZone($location->timezone));
                    $inmersion = BuyInmersion::where("teacher_id",$availableCalendar->user_id)->where("inmersion_start","<=",$localTime->format("Y-m-d"))->where("inmersion_end",">=",$localTime->format("Y-m-d"))->first();

                    if($inmersion) {
                        $localTime = $localTime->format("H:i:s");

                        if($subscriptionType=="real") {
                            if($inmersion->hour_format=="AM" && $localTime>="08:30:00" && $localTime<="12:30:00") {
                                $verify_inmersion = true;
                            }

                            if($inmersion->hour_format=="PM" && $localTime>="13:30:00" && $localTime<="17:30:00") {
                                $verify_inmersion = true;
                            }
                        }elseif($subscriptionType=="dele") {
                            if($inmersion->hour_format=="AM" && $localTime>="08:00:00" && $localTime<="12:30:00") {
                                $verify_inmersion = true;
                            }

                            if($inmersion->hour_format=="PM" && $localTime>="13:00:00" && $localTime<="17:30:00") {
                                $verify_inmersion = true;
                            }
                        }
                    }
                }

                $blocked_time = clone $selected;
                $blocked_time->setTimezone(new \DateTimeZone($availableCalendar->user->timezone));
                $not_available=false;

                $blocked_day = BlockDay::where("teacher_id",$availableCalendar->user_id)->where("blocking_day",$blocked_time->format("Y-m-d"))->first();

                if($blocked_day && isset($blocked_day->from) && isset($blocked_day->till)) {

                    $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                    $time_from->setTimezone(new \DateTimeZone($availableCalendar->user->timezone));
                    //Log::info($time_from->format("h:i:sa"));

                    $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                    $time_till->setTimezone(new \DateTimeZone($availableCalendar->user->timezone));
                    //Log::info($time_till->format("h:i:sa"));

                    if($blocked_time->format("H:i:s") >= $time_from->format("H:i:s") && $blocked_time->format("H:i:s") <= $time_till->format("H:i:s")) {
                        //Log::info($time_from->format("H:i:sa")." ".$blocked_time->format("H:i:sa")." ".$time_till->format("H:i:sa"));
                        $not_available = true;
                    }

                }elseif($blocked_day && !isset($blocked_day->from) && !isset($blocked_day->till)) {
                    $not_available = true;
                }

                if($class || $verify_inmersion || $not_available){
                    continue;
                }

                if(!isset($classes[$selected->format("Y-m-d H:i:s")])){
                    $classes[$selected->format("Y-m-d H:i:s")]=[$availableCalendar->user_id];
                } else {
                    $classes[$selected->format("Y-m-d H:i:s")][]=$availableCalendar->user_id;
                }

            };

        };

        foreach($classes as $k=>&$class){
            $class=array_unique($class);
            if(empty($class)){
                unset($class);
            }
            $class=User::whereIn("id",$class)->get();
        };

        $repeated_teachers=[];
        foreach($classes as $k=>&$class){
            $checking_dele=false;
            $dateTimeNext=\DateTime::createFromFormat("Y-m-d H:i:s",$k)->modify('+30 minutes');
            $dateTimeNext = Classes::fixTime($dateTimeNext);
            if($subscriptionType=="dele" && !$extra_dele){
                $checking_dele=true;
                $dateTimeNext=$dateTimeNext->modify('+30 minutes');
            }

            while(isset($classes[$dateTimeNext->format("Y-m-d H:i:s")]))
            {

                $next_class=$classes[$dateTimeNext->format("Y-m-d H:i:s")];

                foreach($class as &$teacher){
                    /*
                    if($checking_dele && !$teacher->is_deleteacher){
                        continue;
                    }
                    */

                    if(!isset($repeated_teachers[$teacher->id])){
                        $repeated_teachers[$teacher->id]=0;
                    }

                    if(!$next_class->contains($teacher)){
                        continue;
                    };

                    $repeated_teachers[$teacher->id]++;

                };
                $dateTimeNext=$dateTimeNext->modify('+30 minutes');
                if($subscriptionType=="dele" && !$extra_dele){
                    $dateTimeNext=$dateTimeNext->modify('+30 minutes');
                }
            }
        }


        foreach($classes as $k=>&$class){
            foreach($class as &$teacher){
                if(!isset($repeated_teachers[$teacher->id])){
                    $repeated_teachers[$teacher->id]=0;
                }
                $teacher->priority=$repeated_teachers[$teacher->id];
            }
            $class=$class->sortBy("first_name")->sortByDesc("priority")->values();
        }

        ksort($classes);
        $first_hour=key($classes);

        if(count($classes)<1){
            Log::error('Error on Choose teacher Classes, Summary Classes: '.var_export($classes,true). ' FOR: '.$user->email.' With request: '.var_export($request->all(),true));
            if(isset($location_id)) {
                return redirect()->route('classes_in_person_new')->withErrors('Error with selected classes, Please try again.');
            }else {
                return redirect()->route('classes_new')->withErrors('Error with selected classes, Please try again.');
            }
        }

        return view("calendar.choose",["menu_active"=>$menu_active,"classes"=>$classes,"breadcrumb"=>true,"first_hour"=>$first_hour,"location_id"=>$location_id]);
    }

    public function getNewClass($teacher_id=false){
        $user = User::getCurrent();

        $location_id = null;
        $menu_active = "classes_new";
        $route = \Route::currentRouteName();
        $classes_in_person = false;
        $subscriptionType = session("current_subscription");
        $subscription = DB::table('subscriptions')->where('user_id', $user->id)->whereIn('status', ["active", "in_trial", "future"])->get();
        foreach ($subscription as $subscription_plan){
            Session::put('subscription_plan', $subscription_plan->plan_name);
            Session::put('subscription_plan_status', $subscription_plan->status);
        }

        if($route=="classes_in_person_new" || $route=="classes_user_new_teacher"){
            if($user->location_id){
                $location_id = $user->location_id;
                $menu_active = "classes_in_person_new";
                $classes_in_person = true;
                Log::info("User: ".$user->id." entering the class schedule in person");

                if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule){

                    if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                        $location = Location::where("name", "medellin")->first();
                        $location_id = $location->id;
                        $user->location_id = $location->id;
                        $menu_active = "classes_in_person_new";
                        $classes_in_person = true;
                        Log::info("User: ".$user->id." entering the class schedule in person - ActiveLocation");

                        if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite"])){
                            $subscriptionType = "real";
                        }else{
                            $subscriptionType = "dele";
                        }
                    }
                }

            }else {

                if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule){

                    if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                        $location = Location::where("name", "medellin")->first();
                        $location_id = $location->id;
                        $user->location_id = $location->id;
                        $menu_active = "classes_in_person_new";
                        $classes_in_person = true;
                        Log::info("User: ".$user->id." entering the class schedule in person - ActiveLocation");

                        if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite"])){
                            $subscriptionType = "real";
                        }else{
                            $subscriptionType = "dele";
                        }
                    }

                }else{
                    return redirect()->route('classes_new');
                }

            }
        }else{

            if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule){
                if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                    if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite"])){
                        $subscriptionType = "real";
                    }else{
                        $subscriptionType = "dele";
                    }
                }
            }

        }

        if($user->isInmersionStudent() && !$user->isInmersionActive() && !$user->subscribed()){
            return view("calendar.not_book_new_class",["menu_active"=>$menu_active]);
        }

        $teacher = false;

        if(!$user->zoom_email && !$classes_in_person){
            return redirect()->route("profile_zoom_email");
        }


        $subscriptionType=session("current_subscription");


        if($user->favorite_teacher){
            if($classes_in_person) {
                $teachers = collect();
                $verify_teachers = Role::where('name','teacher')->first()->users()->where("id","<>",$user->favorite_teacher)->where("activated",1)->orderBy("first_name","asc")->get();

                foreach($verify_teachers as $teacher) {
                    if($teacher->hasLocation($user->location_id)) {
                        $teachers->push($teacher);
                    }
                }
            }else {
                $teachers = Role::where('name','teacher')->first()->users()->where("id","<>",$user->favorite_teacher)->where("activated",1)->where("block_online",0)->orderBy("first_name","asc")->get();
            }
        } else{
            if($classes_in_person) {
                $teachers = collect();
                $verify_teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();

                foreach($verify_teachers as $teacher) {
                    if($teacher->hasLocation($user->location_id)) {
                        $teachers->push($teacher);
                    }
                }
            }else {
                $teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->where("block_online",0)->orderBy("first_name","asc")->get();
            }
        }

        if($subscriptionType=="real" && $route!="classes_in_person_new" && $route!="classes_user_new_teacher"){
            $teachers=$teachers->where("is_deleteacher",0);
            if($teacher_id){
                //$teacher=User::where("id",$teacher_id)->where("is_deleteacher",0)->where("activated",1)->first(); //old
                $teacher=User::where("id",$teacher_id)->where("activated",1)->first(); //new
            }

        } elseif($route!="classes_in_person_new" && $route!="classes_user_new_teacher") {
            $teachers=$teachers->where("is_deleteacher",1);
            if($teacher_id){
                $teacher=User::where("id",$teacher_id)->where("is_deleteacher",1)->where("activated",1)->first();
            }
        } else {
            if($teacher_id){
                $teacher=User::where("id",$teacher_id)->where("activated",1)->first();
            }
        }

        if($teacher_id && !$teacher){
            if($classes_in_person) {
                return redirect()->route("classes_in_person_new");
            }else {
                return redirect()->route("classes_new");
            }
        }

        $array_one=collect();
        $array_two=collect();
        $array_final=collect();

        $teachers_arr = array();
        foreach($teachers as $teacher){
            $teachers_arr[] = $teacher->id;
        }
 
        $userEvaluations=UserEvaluation::whereIn("teacher_id",$teachers_arr)->where("user_id",$user->id)->get();
        foreach($teachers as $teacher){
            $userEvaluation = $userEvaluations->where("teacher_id",$teacher->id)->first();
            if($userEvaluation){
                $teacher->evaluation_student=$userEvaluation->evaluation;
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

        if($classes_in_person && count($array_final)==0){
            Log::info("There are no teachers assigned for this location: ".$user->location_id);
            return redirect()->route("dashboard");
        }

        return view("calendar.new",["is_person"=>$route, "menu_active"=>$menu_active,"teacher_id"=>$teacher_id,"teachers"=>$array_final,"location_id"=>$location_id]);
    }

    private function sortCalendarByTime($a,$b){
        return strcmp($a->time, $b->time);
    }

    public function getAlarm(){
        $user = User::getCurrent();

        if(!$user){
            return "";
        }

        $subscriptionType=session("current_subscription");
        $leftminutes=false;
        $class=false;
        $nowClass=false;

        $next_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"))->modify('+15 minutes');
        $last_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"))->modify('-15 minutes');

        if($user->getCurrentRol()->name=="teacher"){

            $class=Classes::where("class_time",">=",$last_time->format("Y-m-d H:i:s"))->where("class_time","<=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("teacher_id",$user->id)->first();
            if($class){
                $nowClass=true;
            }

            if(!$class){
                $class=Classes::where("class_time",">=",gmdate("Y-m-d H:i:s"))->where("class_time","<=",$next_time->format("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("teacher_id",$user->id)->first();
                $nowClass=false;
            }

            if($class){
                $class_time=\DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time);
                $class_time = Classes::fixTime($class_time);
                $now_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                $leftminutes=ceil(($class_time->format("U")-$now_time->format("U"))/60);
            }

        } elseif($user->getCurrentRol()->name=="student") {
            $class=Classes::where("class_time",">=",$last_time->format("Y-m-d H:i:s"))->where("class_time","<=",gmdate("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("type",$subscriptionType)->where("user_id",$user->id)->first();
            if($class){
                $nowClass=true;
            }

            if(!$class){
                $class=Classes::where("class_time",">=",gmdate("Y-m-d H:i:s"))->where("class_time","<=",$next_time->format("Y-m-d H:i:s"))->orderBy("class_time","asc")->where("type",$subscriptionType)->where("user_id",$user->id)->first();
                $nowClass=false;
            }

            if($class){
                $class_time =\DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time);
                $class_time = Classes::fixTime($class_time);
                $now_time=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                $leftminutes=ceil(($class_time->format("U")-$now_time->format("U"))/60);
            }
        }
        return view("calendar.includes.alarm",["user"=>$user,"class"=>$class,"nowClass"=>$nowClass,"leftminutes"=>$leftminutes]);
    }

    public function getCalendar($teacher_id=false){
        set_time_limit(0);
        $user = User::getCurrent();
        $user_role = $user->getCurrentRol()->name;
        $user_location = Location::find($user->location_id);
        $route = \Route::currentRouteName();
        $calendar_in_person = false;
        $verify_active_location = false;
        $subscriptionType = session("current_subscription")=="real"?"real":"dele";
        $extra_dele = false;
        if($route=="calendar_in_person_all" || $route=="calendar_in_person_teacher"){
            Log::info("User obtaining the calendar of the school teachers");
            $calendar_in_person = true;
            if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule){
                if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                    $location = Location::where("name", "medellin")->first();
                    $user->location_id = $location->id;
                    Log::info("Get calendar - ActiveLocation");
                    $extra_dele=true;
                    if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite"])){
                        $subscriptionType = "real";
                    }else{
                        $subscriptionType = "dele";
                    }
                    $verify_active_location = true;
                }
            }
        } else {
            if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule){
                if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite","medellin_DELE"])){
                    $extra_dele=true;
                    if(in_array($user->active_locations->plan,["medellin_RW","medellin_RW_1199","medellin_RW_Lite"])){
                        $subscriptionType = "real";
                    }else{
                        $subscriptionType = "dele";
                    }
                    $verify_active_location = true;
                }
            }
        }
        $days=[];
        $calendarDays=[];
        if($subscriptionType=="real"){
            if($calendar_in_person) {
                $calendar = collect();
                $verify_calendar = UserCalendar::orderBy("day","asc")->get();
                foreach($verify_calendar as $cal) {
                    Log::info('teacher has location: '.$user->location_id);
                    Log::info('teacher has location: '.$cal->user->hasLocation($user->location_id));
                    if($cal->user && $cal->user->hasLocation($user->location_id)) {
                        $calendar->push($cal);
                    }
                }
            }else{
                //var_dump($calendar); die();
                $calendar=UserCalendar::whereHas('user', function ($query) {$query->where('is_deleteacher', 0)->where("block_online", 0);})->orderBy("day","asc")->get();

            }
        } else {
            if($calendar_in_person) {
                $calendar = collect();
                $verify_calendar = UserCalendar::orderBy("day","asc")->get();
                foreach($verify_calendar as $cal) {
                    if($cal->user && $cal->user->hasLocation($user->location_id)) {
                        $calendar->push($cal);
                    }
                }
            }else {
                $calendar=UserCalendar::whereHas('user', function ($query) {$query->where('is_deleteacher', 1)->where("block_online", 0);})->orderBy("day","asc")->get();
            }
        }
        if($teacher_id){
            $calendar=$calendar->where("user_id",$teacher_id);
        }
        $arr_uid=array();
        foreach($calendar as $k=>&$teacher_interval){
             $arr_uid[]=$teacher_interval->user_id;
        }

        $userArray = array();
        $userArray_raw = DB::table('users')->select('id','timezone','activated')->whereIn("id", $arr_uid)->get();
        $object = json_decode(json_encode($userArray_raw->toArray()), True);
        foreach ($object as $value){
            $userArray[] = $value['id'];
            $arr_uid_timezone[$value['id']] = $value['timezone'];
        }
        
        $userActiveArray = array();
        $userActiveArray_raw = $userArray_raw->where("activated", 1);
        $object_active = json_decode(json_encode($userActiveArray_raw->toArray()), True);
        foreach ($object_active as $value){
            $userActiveArray[] = $value['id'];
        }
        $userInactiveArray = array_diff($arr_uid, $userActiveArray);

        $userTeacherArray = array();
        $userTeacherArray_raw = DB::table('role_user')->select('user_id')->whereIn("user_id", $userActiveArray)->where("role_id", 3)->get();
        $object_teacher = json_decode(json_encode($userTeacherArray_raw->toArray()), True);
        foreach ($object_teacher as $value){

            $userTeacherArray[] = $value['user_id'];
        }
        $userNonTeacherArray = array_diff($userActiveArray, $userTeacherArray);

        $usersDeleted = array_merge($userInactiveArray,$userNonTeacherArray);
        UserCalendar::whereIn('user_id', $usersDeleted)->delete();

        $arr_uid_lid=array();
        $arr_uid_lid_raw = DB::table('users_location')->whereIn("user_id", $arr_uid)->get();
        $object_uid_lid = json_decode(json_encode($arr_uid_lid_raw->toArray()), True);
        foreach ($object_uid_lid as $value){
            $arr_uid_lid[$value['user_id']] = $value['location_id'];
        }
        $userTime=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
        $limitTime=clone $userTime;
        $userStart=clone $userTime;
		if(!$user->timezone){
			$time_zone_to = "UTC";
		}
		else{
			$time_zone_to = $user->timezone;                    
		}
        $userStart->setTimezone(new \DateTimeZone($time_zone_to));
		$limitTime->setTimezone(new \DateTimeZone($time_zone_to));
        $userTime->setTimezone(new \DateTimeZone($time_zone_to))->sub(new \DateInterval('P1D'));
        $releaseTime = clone $limitTime;
		$releaseTime->setTimezone(new \DateTimeZone("UTC"));
		$releaseTime = \DateTime::createFromFormat("Y-m-d H:i:s", $releaseTime->format("Y-m-d")." "."19:00:00");
		$releaseTime->setTimezone(new \DateTimeZone($time_zone_to));
        if($limitTime->format("Y-m-d H:i:s") > $releaseTime->format("Y-m-d H:i:s")){
            if($teacher_id && $user->favorite_teacher && $user->favorite_teacher==$teacher_id){
                $limitTime->add(new \DateInterval('P6D'));
            } else {
                $limitTime->add(new \DateInterval('P4D'));
            }
            //5 días desde hoy
        } else {
            if($teacher_id && $user->favorite_teacher && $user->favorite_teacher==$teacher_id){
                $limitTime->add(new \DateInterval('P5D'));
            } else {
                $limitTime->add(new \DateInterval('P3D'));
            }
            //4 días desde hoy
        };
        while($limitTime->format("Y-m-d")>=$userTime->format("Y-m-d")){
            $days[$userTime->format("N")]=$userTime->format("Y-m-d");
            $userTime->add(new \DateInterval('P1D'));
        }
        $days[$userTime->format("N")]=$userTime->format("Y-m-d");
        $last_date = $userTime->format("Y-m-d");

        $student_classes = array();
        $student_classes_raw=$user->classes->where("class_time",">=",gmdate("Y-m-d H:i:s"));
        $object_student_classes = json_decode(json_encode($student_classes_raw->toArray()), True);
        foreach ($object_student_classes as $value){
            $student_classes[$value['class_time']] = $value['class_time'];
        }
        foreach($calendar as $teacher_interval){
            if(!$teacher_interval || !isset($days[$teacher_interval->day])){
                continue;
            }
            $arr_uid[]=$teacher_interval->user_id; 
        }
        $teacher_classes = array();
        $teacher_classes_raw = DB::table('classes')->select('teacher_id', 'class_time')->whereIn("teacher_id", $arr_uid)->where("class_time",">=",gmdate("Y-m-d H:i:s"))->get();
        $object_teacher_classes = json_decode(json_encode($teacher_classes_raw->toArray()), True);
        foreach ($object_teacher_classes as $value){
            $teacher_classes[$value['teacher_id']."_".$value['class_time']] = $value['teacher_id']."_".$value['class_time'];
        }
        $teacher_block_days = null;
        $teacher_block_days = DB::table('block_days')->select('teacher_id', 'blocking_day', 'from', 'till')->whereIn("teacher_id", $arr_uid)->get();
        $teacher_blk_days = array();
        foreach($teacher_block_days as $block_day) {
            $teacher_blk_days[$block_day->teacher_id."_".$block_day->blocking_day] = $block_day;
        }

        $teacher_buy_inmersions = null;
        $teacher_buy_inmersions = DB::table('buy_inmersions')->select('user_id','teacher_id', 'inmersion_start', 'inmersion_end', 'hour_format')->whereIn("teacher_id", $arr_uid)->get();

        $user_buy_inmersions = null;
        $user_buy_inmersions = DB::table('buy_inmersions')->select('user_id','teacher_id', 'inmersion_start', 'inmersion_end', 'hour_format')->where("user_id", $user->id)->get();

		foreach($calendar as $teacher_interval){
            if(!$teacher_interval || !isset($days[$teacher_interval->day])){
                continue;
            }
            $startTime=\DateTime::createFromFormat("Y-m-d H:i:s",$days[$teacher_interval->day].' '.$teacher_interval->from);
            $startTime = Classes::fixTime($startTime);
            $startTime->setTimezone(new \DateTimeZone($user->timezone));
            $teacher_limit=\DateTime::createFromFormat("Y-m-d H:i:s",$days[$teacher_interval->day].' '.$teacher_interval->till)->modify('-30 minutes');;
            $teacher_limit = Classes::fixTime($teacher_limit);
            if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                $teacher_limit->modify('-30 minutes');;
            }
            if($teacher_interval->till=="00:00:00"){
                $teacher_limit->add(new \DateInterval('P1D'));
            };
            $teacher_limit->setTimezone(new \DateTimeZone($user->timezone));
            $last_iteration=true;
            $generating_calendar=true;
            //Perfect
            if($startTime->format('Y-m-d H:i:s') == $teacher_limit->format('Y-m-d H:i:s')){
                continue;
            };
            while($generating_calendar){
                while($startTime->format("Y-m-d")<$userStart->format("Y-m-d")) {
                    $startTime->modify('+30 minutes');
                    if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                        $startTime->modify('+30 minutes');
                    }
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                    if(!$last_iteration){
                        $generating_calendar=false;
                    }
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                }
                $serverTime=clone ($startTime);
                $serverTime->setTimezone(new \DateTimeZone("UTC"));
                //if hour exist don't add it => continue.
                $teacher_class = false;
                if($subscriptionType=='dele' && !$calendar_in_person){
                    $dele_time = clone $serverTime;
                    $dele_time = $dele_time->modify('+30 minutes');
                    $dele_time = $dele_time->format("Y-m-d H:i:s");
                    if(is_int($startTime->getOffset()/3600)){
                        if($startTime->format('i:s')!='30:00'){
                            if(!$teacher_class && array_key_exists($teacher_interval->user_id."_".$dele_time, $teacher_classes)){
                               $teacher_class=$teacher_classes[$teacher_interval->user_id."_".$dele_time];
                            }
                        } else {
                            $teacher_class=true;
                        }
                    } else {
                        if($startTime->format('i:s')!='00:00'){
                            if(!$teacher_class && array_key_exists($teacher_interval->user_id."_".$dele_time, $teacher_classes)){
                               $teacher_class=$teacher_classes[$teacher_interval->user_id."_".$dele_time];
                            }
                        } else {
                            $teacher_class=true;
                        }
                    }
                } elseif($subscriptionType=='dele'){
                    $dele_time = clone $serverTime;
                    $dele_time = $dele_time->modify('-30 minutes');
                    $dele_time = $dele_time->format("Y-m-d H:i:s");
                    if(is_int($startTime->getOffset()/3600)) {
                        if ($startTime->format('i:s') == '30:00') {
                            if(!$teacher_class && array_key_exists($teacher_interval->user_id."_".$dele_time, $teacher_classes)){
                               $teacher_class=$teacher_classes[$teacher_interval->user_id."_".$dele_time];
                            }
                        }
                    } else {
                        if ($startTime->format('i:s') == '00:00') {
                            if(!$teacher_class && array_key_exists($teacher_interval->user_id."_".$dele_time, $teacher_classes)){
                                $teacher_class=$teacher_classes[$teacher_interval->user_id."_".$dele_time];
                            }
                        }
                    }
                } elseif(array_key_exists($teacher_interval->user_id."_".$serverTime->format("Y-m-d H:i:s"), $teacher_classes)) {
                    $teacher_class=$teacher_classes[$teacher_interval->user_id."_".$serverTime->format("Y-m-d H:i:s")];
                }
                if($teacher_class){
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                    $startTime->modify('+30 minutes');
                    if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                        $startTime->modify('+30 minutes');
                    }
                    if(!$last_iteration){
                        $generating_calendar=false;
                    }
                    if($subscriptionType=="dele") {
                        $last_iteration=$startTime->format("H:i")<=$teacher_limit->format("H:i");
                        if(!$last_iteration){
                            $generating_calendar=false;
                        }
                    } else {
                        $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i"); 
                    }
                    continue;
                }

                if($user_role!="coordinator"){
                    $serverTime->setTimezone(new \DateTimeZone($user->timezone));
                    $classTime=\DateTime::createFromFormat("Y-m-d H:i:s",$serverTime->format("Y-m-d H:i:s"),new \DateTimeZone($user->timezone));
                    $classTime = Classes::fixTime($classTime);
                    $classTimeOffset=\DateTime::createFromFormat("Y-m-d H:i:s",$serverTime->format("Y-m-d H:i:s"),new \DateTimeZone($user->timezone))->sub(new \DateInterval("PT24H"));
                    if($classTimeOffset->getOffset()!=$classTime->getOffset()) {
                        $offsetInterval = $classTimeOffset->getOffset() - $classTime->getOffset();
                        if($offsetInterval>0){
                            $classTimeOffset = \DateTime::createFromFormat("Y-m-d H:i:s",$classTime->format("Y-m-d H:i:s"))->sub(new \DateInterval("PT".$offsetInterval."S"));
                        } else {
                            $offsetInterval*=-1;
                            $classTimeOffset = \DateTime::createFromFormat("Y-m-d H:i:s",$classTime->format("Y-m-d H:i:s"))->add(new \DateInterval("PT".$offsetInterval."S"));
                        }
                        $classTimeOffset->setTimezone(new \DateTimeZone($user->timezone));
                        $classTimeOffset = Classes::fixTime($classTimeOffset);
                        if($classTime->format("Y-m-d H:i:s")==$classTimeOffset->format("Y-m-d H:i:s")){
                            $classTime = clone ($classTimeOffset);
                        };
                    }
                    $student_class = false;
                    $classTime->setTimezone(new \DateTimeZone("UTC"));
                    if(array_key_exists($classTime->format("Y-m-d H:i:s"), $student_classes)){
                        $student_class=true;
                    }
                } else {
                    $student_class=false;
                }
                if($student_class){
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                    $startTime->modify('+30 minutes');
                    if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                        $startTime->modify('+30 minutes');
                    }
                    if(!$last_iteration){
                        $generating_calendar=false;
                    }
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");    
                    continue;
                }

                $serverTime->setTimezone(new \DateTimeZone("UTC"));
                $verify_inmersion = false;
                if($user_location) {
                    $localTime = clone $serverTime;
                    $inmersion_user = $user_buy_inmersions->where("user_id",$user->id)->where("inmersion_start","<=",$serverTime->format("Y-m-d"))->where("inmersion_end",">=",$serverTime->format("Y-m-d"))->first();

                    if($inmersion_user && $user_location->name == "medellin") {
                        $localTime = $localTime->setTimezone(new \DateTimeZone($user_location->timezone));
                        $localTime = $localTime->format("H:i:s");
                        if($subscriptionType=="real") {
                            if($inmersion_user->hour_format=="AM" && $localTime>="08:30:00" && $localTime<="12:30:00"){
                                $verify_inmersion = true;
                            }
                            if($inmersion_user->hour_format=="PM" && $localTime>="13:30:00" && $localTime<="17:30:00") {
                                $verify_inmersion = true;
                            }
                        }elseif($subscriptionType=="dele") {
                            if($inmersion_user->hour_format=="AM" && $localTime>="08:00:00" && $localTime<="12:30:00") {
                                $verify_inmersion = true;
                            }
                            if($inmersion_user->hour_format=="PM" && $localTime>="13:00:00" && $localTime<="17:30:00") {
                                $verify_inmersion = true;
                            }
                        }
                    }                    
                    if(!$verify_inmersion) {
                        $localTime = clone $serverTime;
                        $inmersion = $teacher_buy_inmersions->where("teacher_id",$teacher_interval->user_id)->where("inmersion_start","<=",$serverTime->format("Y-m-d"))->where("inmersion_end",">=",$serverTime->format("Y-m-d"))->first();
                        if($inmersion) {
                            $localTime = $localTime->setTimezone(new \DateTimeZone($user_location->timezone))->format("H:i:s");
                            if($subscriptionType=="real") {
                                if($inmersion->hour_format=="AM" && $localTime>="08:30:00" && $localTime<="12:30:00") {
                                    $verify_inmersion = true;
                                }
                                if($inmersion->hour_format=="PM" && $localTime>="13:30:00" && $localTime<="17:30:00") {
                                    $verify_inmersion = true;
                                }
                            }elseif($subscriptionType=="dele") {
                                if($inmersion->hour_format=="AM" && $localTime>="08:00:00" && $localTime<="12:30:00") {
                                    $verify_inmersion = true;
                                }
                                if($inmersion->hour_format=="PM" && $localTime>="13:00:00" && $localTime<="17:30:00") {
                                    $verify_inmersion = true;
                                }
                            }
                        }                        
                    }
                }else {
                    if(array_key_exists($teacher_interval->user_id, $arr_uid_lid) && ($arr_uid_lid[$teacher_interval->user_id] == $user->location_id)) {
                        if($user_location) {
                            $localTime = clone $serverTime;
                            $inmersion = $teacher_buy_inmersions->where("teacher_id",$teacher_interval->user_id)->where("inmersion_start","<=",$serverTime->format("Y-m-d"))->where("inmersion_end",">=",$serverTime->format("Y-m-d"))->first();
                            if($inmersion) {
                                $localTime = $localTime->setTimezone(new \DateTimeZone($user_location->timezone))->format("H:i:s");
                                if($subscriptionType=="real") {
                                    if($inmersion->hour_format=="AM" && $localTime>="08:30:00" && $localTime<="12:30:00") {
                                        $verify_inmersion = true;
                                    }
                                    if($inmersion->hour_format=="PM" && $localTime>="13:30:00" && $localTime<="17:30:00") {
                                        $verify_inmersion = true;
                                    }
                                }elseif($subscriptionType=="dele") {
                                    if($inmersion->hour_format=="AM" && $localTime>="08:00:00" && $localTime<="12:30:00") {
                                        $verify_inmersion = true;
                                    }
                                    if($inmersion->hour_format=="PM" && $localTime>="13:00:00" && $localTime<="17:30:00") {
                                        $verify_inmersion = true;
                                    }
                                }
                            }
                        }
                    }
                }
                if($verify_inmersion){
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                    $startTime->modify('+30 minutes');
                    if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                        $startTime->modify('+30 minutes');
                    }
                    if(!$last_iteration){
                        $generating_calendar=false;
                    }
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");    
                    continue;
                }
                $studentTimezone = clone $serverTime;
                $teacherTimezone = $studentTimezone->getTimezone();         
                if(array_key_exists($teacher_interval->user_id, $arr_uid_timezone)){
                    $teacherTimezone = new \DateTimeZone($arr_uid_timezone[$teacher_interval->user_id]);
                    $studentTimezone->setTimezone($teacherTimezone);
                }

                $not_available = false;
                if(array_key_exists($teacher_interval->user_id."_".$studentTimezone->format("Y-m-d"), $teacher_blk_days)){
                    $blocked_day = $teacher_blk_days[$teacher_interval->user_id."_".$studentTimezone->format("Y-m-d")];
                    if(isset($blocked_day->from) && isset($blocked_day->till)) {
                        $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                        $time_from->setTimezone($teacherTimezone);
                        $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                        $time_till->setTimezone($teacherTimezone);
                        if($studentTimezone->format("H:i:s") >= $time_from->format("H:i:s") && $studentTimezone->format("H:i:s") <= $time_till->format("H:i:s")) {
                           $not_available = true;
                        }
                    } else {
                        $not_available = true;
                    }
                }
				if(!$not_available && $user->check_landing_date){
                    $subscription = $user->getCurrentSubscription();
                    if($serverTime->format("Y-m-d") < $subscription->starts_at){
						$not_available = true;
                    }
                }
                if(!$not_available && $verify_active_location && $user->active_locations && $serverTime->format("Y-m-d") < $user->active_locations->activation_day){
                    $not_available = true;
                }
                if($not_available){
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                    $startTime->modify('+30 minutes');
                    if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                        $startTime->modify('+30 minutes');
                    }
                    if(!$last_iteration){
                        $generating_calendar=false;
                    }
                    $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");   
                    continue;
                }
                //here ok
                $setCalendarDays=false;
                if(!isset($calendarDays[$startTime->format("Y-m-d")])){
                    if(!in_array($startTime->format("Y-m-d"),$days)){
                        break;
                    };
                    $calendarDays[$startTime->format("Y-m-d")]=[];
                }
                foreach($calendarDays[$startTime->format("Y-m-d")] as &$calendarDay){
                    if($calendarDay->time==$startTime->format("H:i")){
                        $setCalendarDays=true;
                        $calendarDay->teacher[]=$teacher_interval->user_id;
                        $calendarDay->id[]=$teacher_interval->id;
                    };
                }
                if(!$setCalendarDays){
                    $userTime=\DateTime::createFromFormat("Y-m-d H:i:s",gmdate("Y-m-d H:i:s"));
                    $userTime->setTimezone(new \DateTimeZone($user->timezone));
                    if($userTime->format("Y-m-d")!=$startTime->format("Y-m-d") || $startTime->format("H:i")>$userTime->format("H:i")){
                        $calendarObject = new \stdClass;
                        $calendarObject->time=$startTime->format("H:i");
                        $calendarObject->teacher=[$teacher_interval->user_id];
                        $calendarObject->id=[$teacher_interval->id];
                        $calendarDays[$startTime->format("Y-m-d")][]=$calendarObject;
                    }
                }
                $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
                $startTime->modify('+30 minutes');
                if(($subscriptionType=="dele" && !$extra_dele) || ($calendar_in_person && $teacher_interval->user->is_deleteacher)){
                    $startTime->modify('+30 minutes');
                }
                if(!$last_iteration){
                    $generating_calendar=false;
                }
                $last_iteration=$startTime->format("H:i")!=$teacher_limit->format("H:i");
            }
        };
        $calendarDays= array_filter(array_map('array_filter', $calendarDays));
        foreach($calendarDays as &$calendarDay){
            usort($calendarDay, array($this,'sortCalendarByTime'));
            foreach($calendarDay as $k=>&$hour){
                $hour->continuous=false;
                if(isset($calendarDay[$k+1])){
                    if(!empty(array_intersect($calendarDay[$k+1]->teacher,$hour->teacher))>0){
                        $hour->continuous=true;
                    };
                };
            };
        };
        ksort($calendarDays);
        $userInitTime=\DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new \DateTimeZone($user->timezone));
        while($userInitTime->format("Y-m-d")<$last_date){
            if(!isset($calendarDays[$userInitTime->format("Y-m-d")])){
                $calendarDays[$userInitTime->format("Y-m-d")]=[];
            }
            $userInitTime->add(new \DateInterval("P1D"));
            if(!isset($calendarDays[$userInitTime->format("Y-m-d")])){
                $calendarDays[$userInitTime->format("Y-m-d")]=[];
            }
        }
        ksort($calendarDays);
        $calendarDays=array_slice($calendarDays,0,count($calendarDays)-1);
        $maxs=false;
        if(!empty($calendarDays)){
            $maxs = array_keys($calendarDays, max($calendarDays))[0];
        } else {
            $calendarDays=array_flip($days);
        };
        return view("calendar.includes.calendar",["days"=>$calendarDays,"max"=>$maxs,"teacher"=>$teacher_id]);
    }
}