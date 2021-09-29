<?php

namespace App\Http\Controllers;

use App\Models\Error;
use App\User;
use App\Models\BlockDay;
use App\Models\BuyInmersion;
use App\Models\Classes;
use App\Models\InformationContents;
use App\Models\Inmersion;
use App\Models\InmersionPayment;
use App\Models\Location;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use NumberToWords\NumberToWords;
use DB;

class InmersionController extends Controller
{
	public function getInmersion($location_name, Request $request) {
        $location = Location::where("name",$location_name)->first();

        if(!$location){
            Log::info("Location ".$location_name." does not exist");
            return redirect()->route("login")->withErrors(['The entered location is not registered!']);
        }

        if(isset($request->fname) && isset($request->lname) && isset($request->email)) {
            $request->email = str_replace(' ','+',$request->email);
            $request->referral_code = isset($request->referral_code)?$request->referral_code:'';
            $check_user = User::where("email",$request->email)->first();
            
            if($check_user) {
                Log::info('There is already a user with that email ('.$check_user->email.')');
                return redirect()->route('change_location')->withErrors(['There’s already an account with that email!']);
            }

            $data = [];
            $data["first_name"] = $request->fname;
            $data["last_name"] = $request->lname;
            $data["email"] = $request->email;
            $data["referral_code"] = $request->referral_code;
            $data["timezone"] = $location->timezone;
            $data["activated"] = 1;
            session(["inmersion_password"=>"12345"]);
            $data["password"] = \Hash::make("12345");
            $data["registered_inmersion"] = 1;

            $user = User::create($data);
            $user->detachRoles($user->roles);
            $user->attachRole(Role::where("name","student")->first());

            InmersionPayment::create(["user_id"=>$user->id, "user_registration_day"=>gmdate("Y-m-d")]);

            Log::info("User created from Inmersion - Referral code: ".$user);
            Auth::loginUsingId($user->id, true);

            $result = \ChargeBee_Customer::create([
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'customFields' => [
                    'referral_code' => $user->referral_code,
                ],
            ]);

            if($result->customer()->id) {
                $user->chargebee_id = $result->customer()->id;
                User::where("id",$user->id)->update(['chargebee_id'=>$result->customer()->id]);
                //Log::info("Customer Create For: ".$user->email." Status: ". var_export($result->success,true));
                Log::info("New Chargebee ID: ".$result->customer()->id);
            }else {
                Log::info("Error creating the Chargebee ID! - User: ".$user->email);
            }

            return redirect()->route('inmersion',['location'=>$location->name]);
        }

        $user = User::getCurrent();

        if(!$user) {
            Log::info("There is no user with active session");
        }else {
            if(!$user->location_id) {
                Log::info("The user ".$user->id." does not belong to a school");
            }
        }

        $teachers = collect();
        $verify_teachers = Role::where('name', 'teacher')->first()->users()->where("activated", 1)->orderBy("first_name","ASC")->get();

        $arr_uid=array();
        foreach($verify_teachers as $k=>&$teacher){
             $arr_uid[]=$teacher->id;
        }
		
		$arr_uid_lid=array();
        $arr_uid_lid_raw = DB::table('users_location')->whereIn("user_id", $arr_uid)->get();
        $object_uid_lid = json_decode(json_encode($arr_uid_lid_raw->toArray()), True);
        foreach ($object_uid_lid as $value){
			if(!array_key_exists($value['user_id'], $arr_uid_lid) || $arr_uid_lid[$value['user_id']] != $location->id)
			{
				$arr_uid_lid[$value['user_id']] = $value['location_id'];
			}
        }
		
        $arr_teacher_id=array();
        foreach($verify_teachers as $teacher) {
            if(array_key_exists($teacher->id, $arr_uid_lid) && $arr_uid_lid[$teacher->id] == $location->id) {
                $teachers->push($teacher);
				$arr_teacher_id[]=$teacher->id;
            }
        }

        Log::info("Teachers: ".count($teachers));

        if(count($teachers)==0) {
            Log::info("There are no teachers available");
            return redirect()->route("dashboard");
        }

        Log::info("Calendar of Inmersion");

        $start_week = \DateTime::createFromFormat("U",strtotime('monday this week'),new \DateTimeZone("UTC"))->add(new \DateInterval("P7D"));

        $final_week = clone $start_week;
        $final_week = $final_week->add(new \DateInterval("P24M"));

        Log::info("Start week: ".$start_week->format("Y-m-d"));
        Log::info("Final week: ".$final_week->format("Y-m-d"));

        $calendars=[];
        $count=0;

        while($start_week->format("Y-m-d") < $final_week->format("Y-m-d")) {
            $week_end = clone $start_week;
            $week_end = $week_end->add(new \DateInterval("P26D"));

            if(!isset($calendars[$start_week->format("Y-m")])){
                $calendars[$start_week->format("Y-m")]=collect();
                $count++;
            }

            $count_am = 0;
            $count_pm = 0;
            $blocked_teachers_am = array();
            $blocked_teachers_pm = array();

            $check_start_week = clone $start_week;
            $check_start_week = $check_start_week->sub(new \DateInterval("P7D"));
            $check_week_end = clone $week_end;
            $check_week_end = $check_week_end->sub(new \DateInterval("P7D"));

            $check_start_week_n = clone $start_week;
            $check_start_week_n = $check_start_week_n->add(new \DateInterval("P7D"));
            $check_week_end_n = clone $week_end;
            $check_week_end_n = $check_week_end_n->add(new \DateInterval("P7D"));

            $am_one_and_two = false;
            $pm_one_and_two = false;

            $count_teachers_final_am = 0;
            $count_teachers_final_pm = 0;

            //blocked day section
            $blocked_days = BlockDay::whereIn("teacher_id", $arr_teacher_id)->where("public_holiday","<>", 1)->where("blocking_day" ,">=", $start_week->format("Y-m-d"))->where("blocking_day", "<=", $week_end->format("Y-m-d"))->get();
            foreach($blocked_days as $blocked_day) {

                if($blocked_day && isset($blocked_day->from) && isset($blocked_day->till)) {

                    $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                    $time_from->setTimezone(new \DateTimeZone($location->timezone));
                    //Log::info($time_from->format("h:i:sa"));

                    if($time_from->format("A")=="AM") {
                        $blocked_teachers_am[] = $blocked_day->teacher_id;
                    } else {
                        $blocked_teachers_pm[] = $blocked_day->teacher_id;
                    }

                    $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                    $time_till->setTimezone(new \DateTimeZone($location->timezone));
                    //Log::info($time_till->format("h:i:sa"));
                    
                    if($time_till->format("A")=="AM") {
                        $blocked_teachers_am[] = $blocked_day->teacher_id;
                    }else {
                        $blocked_teachers_pm[] = $blocked_day->teacher_id;
                    }

                } elseif($blocked_day && !isset($blocked_day->from) && !isset($blocked_day->till)) {
                    $blocked_teachers_am[] = $blocked_day->teacher_id;
                    $blocked_teachers_pm[] = $blocked_day->teacher_id;
                }
            }
            //blocked day section
			
			$immersion_am = BuyInmersion::select('teacher_id')->whereIn("teacher_id", $arr_teacher_id)->where("hour_format", "AM")->where(function ($q) use ($check_start_week,$check_week_end,$start_week,$week_end,$check_start_week_n,$check_week_end_n)
			{
				$q->where(function ($q1) use ($check_start_week,$check_week_end)
				{
					$q1->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"));
				})
				->orWhere(function ($q1) use ($start_week,$week_end)
				{
					$q1->where("inmersion_start", $start_week->format("Y-m-d"))->where("inmersion_end", $week_end->format("Y-m-d"));
				})
				->orWhere(function ($q1) use ($check_start_week_n,$check_week_end_n)
				{
					$q1->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"));
				});
			})->distinct('teacher_id')->get();

            $immersion_am = array_column($immersion_am->toArray(), 'teacher_id');
            $count_am = count(array_unique(array_merge($immersion_am, $blocked_teachers_am)));

			if($user) 
			{
				//immersion user AM
				$immersion_am_user = BuyInmersion::where("user_id", $user->id)->whereIn("teacher_id", $arr_teacher_id)->where("hour_format", "AM")->where(function ($q) use ($check_start_week,$check_week_end,$check_start_week_n,$check_week_end_n)
				{
					$q->where(function ($q1) use ($check_start_week,$check_week_end)
					{
						$q1->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"));
					})
					->orWhere(function ($q1) use ($check_start_week_n,$check_week_end_n)
					{
						$q1->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"));
					});
				})->first();

				if($immersion_am_user) {
					$am_one_and_two = true;
				}
            }
			
			//Inmersion PM
			$immersion_pm = BuyInmersion::select('teacher_id')->whereIn("teacher_id", $arr_teacher_id)->where("hour_format", "PM")->where(function ($q) use ($check_start_week,$check_week_end,$start_week,$week_end,$check_start_week_n,$check_week_end_n)
			{
				$q->where(function ($q1) use ($check_start_week,$check_week_end)
				{
					$q1->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"));
				})
				->orWhere(function ($q1) use ($start_week,$week_end)
				{
					$q1->where("inmersion_start", $start_week->format("Y-m-d"))->where("inmersion_end", $week_end->format("Y-m-d"));
				})
				->orWhere(function ($q1) use ($check_start_week_n,$check_week_end_n)
				{
					$q1->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"));
				});
			})->distinct('teacher_id')->get();
			
            $immersion_pm = array_column($immersion_pm->toArray(), 'teacher_id');
            $count_pm = count(array_unique(array_merge($immersion_pm, $blocked_teachers_pm)));
			
			if($user) 
			{
                //immersion user PM
				$immersion_pm_user = BuyInmersion::where("user_id", $user->id)->whereIn("teacher_id", $arr_teacher_id)->where("hour_format", "PM")->where(function ($q) use ($check_start_week,$check_week_end,$check_start_week_n,$check_week_end_n)
				{
					$q->where(function ($q1) use ($check_start_week,$check_week_end)
					{
						$q1->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"));
					})
					->orWhere(function ($q1) use ($check_start_week_n,$check_week_end_n)
					{
						$q1->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"));
					});
				})->first();

				if($immersion_pm_user) {
					$pm_one_and_two = true;
				}
			}
			
			$classes = Classes::whereIn("teacher_id", $arr_teacher_id)->where("class_time",">=",$start_week->format("Y-m-d"))->where("class_time","<=",$week_end->format("Y-m-d"))->get();
			
			$teachers_am_class = array();
			$teachers_pm_class = array();
			$count_classes_am = 0;
			$count_classes_pm = 0;

			if(count($classes)>0 && $location) {

				foreach($classes as $key => $class) {
					$local_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");

					if($local_time >= "08:00:00" && $local_time <= "12:30:00") {
						$count_classes_am++;
						$teachers_am_class[]=$class->teacher_id;
					}

					if($local_time >= "13:00:00" && $local_time <= "17:30:00") {
						$count_classes_pm++;
						$teachers_pm_class[]=$class->teacher_id;
					}
				}
				
				$count_teachers_final_am=count(array_unique($teachers_am_class));
				$count_teachers_final_pm=count(array_unique($teachers_pm_class));
            }
            
            //Verify AM
            if($count_am==count($teachers) || $count_teachers_final_am==count($teachers)) {
                $calendars[$start_week->format("Y-m")]->push(["start_week"=>$start_week->format("Y-m-d"), "week_end"=>$week_end->format("Y-m-d"), "count"=>$count, "format"=>"AM", "status"=>"0"]);
                Log::info("Week busy - Start week: ".$start_week->format("Y-m-d")." - Week end: ".$week_end->format("Y-m-d")." - Hour format: AM");
            }else {
                $student_class_am = false;
                $inmersion = false;

                if($user) {            
                    $inmersion = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $start_week->format("Y-m-d"))->where("inmersion_end", $week_end->format("Y-m-d"))->where("hour_format", "AM")->first();

                    $student_classes = $user->classes->where("class_time",">=",$start_week->format("Y-m-d"))->where("class_time","<=",$week_end->format("Y-m-d"));

                    if(count($student_classes)>0 && $location) {
                        foreach($student_classes as $key => $class) {
                            $local_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");
                            if($local_time >= "08:00:00" && $local_time <= "12:30:00") {
                                $student_class_am = true;
                            }
                        }
                    }
                }

                if($inmersion || $am_one_and_two || $student_class_am) {
                    $calendars[$start_week->format("Y-m")]->push(["start_week"=>$start_week->format("Y-m-d"), "week_end"=>$week_end->format("Y-m-d"), "count"=>$count, "format"=>"AM", "status"=>"0"]);
                    Log::info("Week occupied by the student who is online: ".$start_week->format("Y-m-d")." - Week end: ".$week_end->format("Y-m-d")." - Hour format: AM");
                }else {
                    $calendars[$start_week->format("Y-m")]->push(["start_week"=>$start_week->format("Y-m-d"), "week_end"=>$week_end->format("Y-m-d"), "count"=>$count, "format"=>"AM", "status"=>"1"]);
                }
            }

            //Verify PM
            if($count_pm==count($teachers) || $count_teachers_final_pm==count($teachers)) {
                $calendars[$start_week->format("Y-m")]->push(["start_week"=>$start_week->format("Y-m-d"), "week_end"=>$week_end->format("Y-m-d"), "count"=>$count, "format"=>"PM", "status"=>"0"]);
                Log::info("Week busy - Start week: ".$start_week->format("Y-m-d")." - Week end: ".$week_end->format("Y-m-d")." - Hour format: PM");
            }else {
                $student_class_pm = false;
                $inmersion = false;

                if($user) { 
                    $inmersion = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $start_week->format("Y-m-d"))->where("inmersion_end", $week_end->format("Y-m-d"))->where("hour_format", "PM")->first();

                    $student_classes = $user->classes->where("class_time",">=",$start_week->format("Y-m-d"))->where("class_time","<=",$week_end->format("Y-m-d"));

                    if(count($student_classes)>0 && $location) {
                        foreach($student_classes as $key => $class) {
                            $local_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");
                            if($local_time >= "13:00:00" && $local_time <= "17:30:00") {
                                $student_class_pm = true;
                            }
                        }
                    }
                }

                if($inmersion || $pm_one_and_two || $student_class_pm) {
                    $calendars[$start_week->format("Y-m")]->push(["start_week"=>$start_week->format("Y-m-d"), "week_end"=>$week_end->format("Y-m-d"), "count"=>$count, "format"=>"PM", "status"=>"0"]);
                    Log::info("Week occupied by the student who is online: ".$start_week->format("Y-m-d")." - Week end: ".$week_end->format("Y-m-d")." - Hour format: PM");
                }else {
                    $calendars[$start_week->format("Y-m")]->push(["start_week"=>$start_week->format("Y-m-d"), "week_end"=>$week_end->format("Y-m-d"), "count"=>$count, "format"=>"PM", "status"=>"1"]);
                }
            }

            $start_week = $start_week->add(new \DateInterval("P7D"));
        }

        $final_calendars=[];
        foreach($calendars as $date => $calendar) {

            foreach($calendar as $key => $cal) {

                if(!isset($final_calendars[$key])){
                    $final_calendars[$key]=collect();

                    for($i=1; $i<=$count ; $i++) {

                        if($i==$cal["count"]) {
                            $final_calendars[$key]->push(["start_week"=>$cal["start_week"], "week_end"=>$cal["week_end"], "count"=>$cal["count"], "format"=>$cal["format"], "status"=>$cal["status"]]);
                        }else {
                            $final_calendars[$key]->push(["start_week"=>null, "week_end"=>null, "count"=>$i, "format"=>"-", "status"=>"-"]);
                        }
                    }
                }else{

                    for($i=1; $i<=$count ; $i++) {

                        if($i==$cal["count"]) {
                            $final_calendars[$key][$i-1] = ["start_week"=>$cal["start_week"], "week_end"=>$cal["week_end"], "count"=>$cal["count"], "format"=>$cal["format"], "status"=>$cal["status"]];
                        }
                    }
                }
            }
        }

        $amount = $location->price;
		if($user && $user->referral_code)
		{
			$discount_amount = $user->getDiscount($user->referral_code);

			if($discount_amount)
			{
				$amount = $amount - ($discount_amount/100);
			}
		}

        return view("inmersion.first_step",["menu"=>"first_step", "calendars"=>$calendars, "final_calendars"=>$final_calendars, "location"=>$location, "amount"=>$amount]);
	}

	public function postCalendar(Request $request) {
        $location_id = $request->input('location_id');
        $selecteds = $request->input('selecteds');

        $user = User::getCurrent();

        $location = Location::find($location_id);

        if(!$location) {
            Log::info("Location ".$location_id." does not exist");
            return redirect()->route("login")->withErrors(['The entered location is not registered!']);
        }

        $count_selecteds = count($selecteds);
        Log::info("Selected weeks: ".$count_selecteds);
        Log::info($selecteds);

        $teachers = collect();
        $verify_teachers = Role::where('name', 'teacher')->first()->users()->where("activated", 1)->orderBy("first_name","ASC")->get();

        foreach($verify_teachers as $teacher) {
            if($teacher->hasLocation($location->id)) {
                $teachers->push($teacher);
            }
        }

        Log::info("Teachers: ".count($teachers));

        $week = new \stdClass();

        foreach($selecteds as $selected) {
            $sel = explode(",",$selected);

            $week->inmersion_start = $sel[0];
            $week->inmersion_end = $sel[1];
            $week->hour_format = $sel[2];

            $check_start_week = \DateTime::createFromFormat("Y-m-d", $sel[0])->sub(new \DateInterval("P7D"));
            $check_week_end = \DateTime::createFromFormat("Y-m-d", $sel[1])->sub(new \DateInterval("P7D"));

            $check_start_week_n = \DateTime::createFromFormat("Y-m-d", $sel[0])->add(new \DateInterval("P7D"));
            $check_week_end_n = \DateTime::createFromFormat("Y-m-d", $sel[1])->add(new \DateInterval("P7D"));

            foreach($teachers as $key => $teacher) {

                $previous_immersion = BuyInmersion::where("teacher_id", $teacher->id)->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"))->where("hour_format", $sel[2])->first();

                $current_inmersion = BuyInmersion::where("teacher_id", $teacher->id)->where("inmersion_start", $sel[0])->where("inmersion_end", $sel[1])->where("hour_format", $sel[2])->first();

                $next_immersion = BuyInmersion::where("teacher_id", $teacher->id)->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"))->where("hour_format", $sel[2])->first();

                $classes = $teacher->teacher_classes()->where("class_time",">=",$sel[0])->where("class_time","<=",$sel[1])->get();

                $teacher_class = false;

                if(count($classes)>0 && $location) {

                    foreach($classes as $class) {
                        $local_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");

                        if($week->hour_format=="AM" && $local_time >= "08:00:00" && $local_time <= "12:30:00") {
                            $teacher_class = true;
                        }

                        if($week->hour_format=="PM" && $local_time >= "13:00:00" && $local_time <= "17:30:00") {
                            $teacher_class = true;
                        }
                    }

                }

                $blocked_day=BlockDay::where("teacher_id",$teacher->id)->where("public_holiday","<>", 1)->where("blocking_day",">=",$sel[0])->where("blocking_day","<=",$sel[1])->first();

                $check_time = false;
                $blocked = false;

                if($blocked_day && isset($blocked_day->from) && isset($blocked_day->till)) {

                    $time_from = \DateTime::createFromFormat("H:i:s",$blocked_day->from);
                    $time_from->setTimezone(new \DateTimeZone($location->timezone));
                    //Log::info($time_from->format("h:i:sa"));

                    if($time_from->format("A")==$week->hour_format) {
                        $check_time = true;
                    }

                    $time_till = \DateTime::createFromFormat("H:i:s",$blocked_day->till);
                    $time_till->setTimezone(new \DateTimeZone($location->timezone));
                    //Log::info($time_till->format("h:i:sa"));
                    
                    if($time_till->format("A")==$week->hour_format) {
                        $check_time = true;
                    }

                }elseif($blocked_day && !isset($blocked_day->from) && !isset($blocked_day->till)) {
                    $blocked = true;
                }

                if($previous_immersion || $current_inmersion || $next_immersion || $teacher_class || $check_time || $blocked) {
                    unset($teachers[$key]);
                }
            }
        }

        if(count($teachers) == 0) {
            return redirect()->route('inmersion',['location'=>$location->name])->withErrors(["Teachers are no longer available for this date!"]);
        }

        $number_to_words = new NumberToWords();
        $number_transformer = $number_to_words->getNumberTransformer('en');
        $availability_teachers = $number_transformer->toWords(count($teachers));

    	return view("inmersion.second_step",["menu"=>"second_step", "week"=>$week, "availability_teachers"=>$availability_teachers, "teachers"=>$teachers, "location"=>$location]);
	}

    public function postCalendarTeacher(Request $request) {
        $location_id = $request->input('location_id');
        $selecteds = $request->input('selecteds');
        $current_user = User::getCurrent();

        $location = Location::find($location_id);

        if(!$location) {
            Log::info("Location ".$location_id." does not exist");
            return redirect()->route("login")->withErrors(['The entered location is not registered!']);
        }

        $count_selecteds = count($selecteds);
        Log::info("Selected weeks with their respective teacher: ".$count_selecteds);
        Log::info($selecteds);

        return view("inmersion.third_step",["menu"=>"third_step", "current_user"=>$current_user, "selecteds"=>$selecteds, "location"=>$location]);
    }

	public function inmersionLogin(Request $request) {
        $email = $request->get("email");
        $password = $request->get("password");

        $check_user = User::where("email",$email)->first();

        if(Auth::attempt(['email' => $email, 'password' => $password], true)) {
            $user = User::getCurrent();
            Log::info('Login from Inmersion: '.$user->id." - ".$user->email);
            if(!$user->activated){
                Auth::logout();
                Log::info('User not activated: '.$user->id." - ".$user->email);
                return response()->json(['response'=>'not_activated']);
            }
            User::where("id",$user->id)->update(["last_login"=>date('Y-m-d H:i:s')]);
            $user->verifyRole();
            $user->updateSubscriptionInfo();
            return response()->json(['response'=>'redirect_pay_deposit']);
        }
        
        Log::error('Fail login in Inmersion: '.$email);
        if(!$check_user){
            Log::info('The email you entered is incorrect: '.$email);
            return response()->json(['response'=>'email_incorrect']);
        }else{
            Log::info('The password you entered is incorrect - Email: '.$email);
            return response()->json(['response'=>'password_incorrect']);
        }

	}

	public function inmersionCreateAccount(Request $request) {
        $user_logged = User::getCurrent();
		$data = $request->only(["first_name", "last_name", "email", "password"]);
        $check_user = User::where("email",$data["email"])->first();

        if($user_logged && $user_logged->email==$data["email"]) {
            if(strlen($data["password"])<5){
                Log::info('Passwords must be at least 5 characters');
                return response()->json(['response'=>'short_password']);
            }else {
                session(["inmersion_password"=>$data["password"]]);
                $data["password"] = \Hash::make($data["password"]);
                User::where("id",$user_logged->id)->update(["password"=>$data["password"]]);
                return response()->json(['response'=>'update_password']);
            }
        }
        
        if($check_user){
            Log::info('There is already a user with that email ('.$check_user->email.')');
        	return response()->json(['response'=>'existing_user']);
        }

        if(strlen($data["password"])<5){
            Log::info('Passwords must be at least 5 characters');
            return response()->json(['response'=>'short_password']);
        }

        $location_id = $request->input('location_id');
        $location = Location::find($location_id);

        $data["timezone"] = $location->timezone;

        $data["activated"] = 1;

        session(["inmersion_password"=>$data["password"]]);

        $data["password"] = \Hash::make($data["password"]);

        $data["registered_inmersion"] = 1;

        /*$type = $request->input('type');
        if(isset($type)){
            $data["location_half"]=1;
        } else {
            $data["location_half"]=0;
        }*/

        $user = User::create($data);

        Log::info("User created from Inmersion: ".$user);

        $user->detachRoles($user->roles);

        $user->attachRole(Role::where("name","student")->first());

        return response()->json(['response'=>'created_user', 'user'=>$user]);
	}

	public function postLogged(Request $request) {
		$user_id = $request->input('user_id');
        $location_id = $request->input('location_id');
        $selecteds = $request->input('selecteds');

        Log::info("Selected weeks to pay:");
        Log::info($selecteds);

        if(isset($user_id)) {
            Auth::loginUsingId($user_id, true);
            $user = User::getCurrent();
        }else{
            $user = User::getCurrent();
        }

        Log::info("User current for pay deposit: ".$user->id);

        $location = Location::find($location_id);

        if(!$location) {
            Log::info("Location ".$location_id." does not exist");
            return redirect()->route("logout");
        }

        $price = $location->price;

        $teachers = [];
        foreach($selecteds as $key => $selected) {
            $sel = explode(",",$selected);
            $teacher = User::find($sel[3]);
            $teachers[] = $teacher;
        }

        $selectedDate = $sel[0];
		$amount = $location->price;
		if($user->referral_code)
		{
            $discount_amount = $user->getDiscount($user->referral_code);
			if($discount_amount)
			{
				$amount = $amount - ($discount_amount/100);
			}
		}
		
		$total_cost_flag = false;
		$second_payment_date = \DateTime::createFromFormat("Y-m-d", $selectedDate)->sub(new \DateInterval("P8D"))->format("Y-m-d");
		if($second_payment_date > gmdate('Y-m-d')) {
			$amount = $amount/2;
			$total_cost_flag = true;
        }
        
        $user->refreshPaymentMethodsInmMed();
        return view("inmersion.four_step",["menu"=>"four_step", "user"=>$user, "selecteds"=>$selecteds, "price"=>$amount, "teachers"=>$teachers, "location"=>$location, "total_cost_flag"=>$total_cost_flag]);
	}

    public function payInmersion(Request $request) {
        $user = User::getCurrent();
        $location_id = $request->input('location_id');
        $selecteds = $request->input('selecteds');

        $location = Location::find($location_id);

        if(!$location) {
            Log::info("Location ".$location_id." does not exist");
            return redirect()->route("logout");
        }

        $weeks = count($selecteds);
        Log::info("First verification of weeks: ".$weeks);

        $inmersion_start = null;
        $inmersion_end = null;
        $hour_format = null;
        $teacher_id = null;

        foreach($selecteds as $key => $selected) {
            $sel = explode(",",$selected);
            $inmersion_start = $sel[0];
            $inmersion_end = $sel[1];
            $hour_format = $sel[2];
            $teacher_id = $sel[3];
            
            if(!$user->timezone){
				$time_zone_from = "UTC";
			}
			else{
				$time_zone_from = $user->timezone;                    
			}
			$time_zone_to = "UTC";
			
			$inmersion_start_date = new \DateTime($inmersion_start, new \DateTimeZone($time_zone_from));
			$inmersion_start_date->setTimezone(new \DateTimeZone($time_zone_to));
			$inmersion_start = $inmersion_start_date->format("Y-m-d");

			$inmersion_end_date = new \DateTime($inmersion_end, new \DateTimeZone($time_zone_from));
			$inmersion_end_date->setTimezone(new \DateTimeZone($time_zone_to));
			$inmersion_end = $inmersion_end_date->format("Y-m-d");

            $check_start_week = \DateTime::createFromFormat("Y-m-d", $inmersion_start)->sub(new \DateInterval("P7D"));
            $check_week_end = \DateTime::createFromFormat("Y-m-d", $inmersion_end)->sub(new \DateInterval("P7D"));

            $check_start_week_n = \DateTime::createFromFormat("Y-m-d", $inmersion_start)->add(new \DateInterval("P7D"));
            $check_week_end_n = \DateTime::createFromFormat("Y-m-d", $inmersion_end)->add(new \DateInterval("P7D"));

            $previous_immersion = BuyInmersion::where("teacher_id", $teacher_id)->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            $current_inmersion = BuyInmersion::where("teacher_id", $teacher_id)->where("inmersion_start", $inmersion_start)->where("inmersion_end", $inmersion_end)->where("hour_format", $hour_format)->first();

            $next_immersion = BuyInmersion::where("teacher_id", $teacher_id)->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            //Previous Inmersion - User active
            $previous_immersion_user = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            //Current Inmersion - User active
            $current_inmersion_user = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $inmersion_start)->where("inmersion_end", $inmersion_end)->where("hour_format", $hour_format)->first();

            //Next Inmersion - User active
            $next_immersion_user = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            $teacher = User::find($teacher_id);
            $teacher_class = false;
            
            if($teacher) {
                $classes = $teacher->teacher_classes()->where("class_time",">=",$inmersion_start)->where("class_time","<=",$inmersion_end)->get();

                if(count($classes)>0 && $location) {

                    foreach($classes as $class) {
                        $local_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");

                        if($hour_format=="AM" && $local_time >= "08:00:00" && $local_time <= "12:30:00") {
                            $teacher_class = true;
                        }

                        if($hour_format=="PM" && $local_time >= "13:00:00" && $local_time <= "17:30:00") {
                            $teacher_class = true;
                        }
                    }

                }
            }

            $blocked_day=BlockDay::where("teacher_id",$teacher->id)->where("public_holiday","<>", 1)->where("blocking_day",">=",$inmersion_start)->where("blocking_day","<=",$inmersion_end)->first();

            if($previous_immersion || $current_inmersion || $next_immersion || $previous_immersion_user || $current_inmersion_user || $next_immersion_user || $teacher_class || $blocked_day) {
                unset($selecteds[$key]);
            }
        }

        $weeks = count($selecteds);
        Log::info("Second verification of weeks: ".$weeks);

        if($weeks==0) {
            return response()->json(['response'=>'weeks_zero']);
        }

        $total_price = $location->price;
        $amount = $total_price;

        try {

            $second_payment_date = \DateTime::createFromFormat("Y-m-d", $inmersion_start)->sub(new \DateInterval("P7D"))->format("Y-m-d");
            $status = 0;

            if($second_payment_date > gmdate('Y-m-d')) {
                $amount = $amount/2;
            }else{
                \Log::info("The total amount is charged because the start date is already greater than the one destined to make the second payment!");
                $status = 1;
            }

            \Log::info("Buy Inmersion for: ".$user->email." - Amount: $".$amount." - PMT: ".$user->payment_method_token);
            $result = \ChargeBee_Transaction::createAuthorization([
                'amount' => $amount,
                'paymentMethodToken' => $user->payment_method_token,
                'options' => [
                    'submitForSettlement' => True,
                ]
            ]);

            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);

            if($result->success){
                BuyInmersion::create(["user_id"=>$user->id, "teacher_id"=>$teacher_id, "total_price"=>$total_price, "inmersion_start"=>$inmersion_start, "inmersion_end"=>$inmersion_end, "hour_format"=>$hour_format, "second_payment_date"=>$second_payment_date, "status"=>$status,"location_id"=>$location_id]);

                try {
                    if (\App::environment('production')) {
                        $teacher = User::find($teacher_id);
                        \Mail::send('emails.user_welcome_inmersion', ["location"=>$location,"user" => $user, "selecteds" => $selecteds, "teacher" => $teacher], function ($message) use ($user) {
                            $message->subject("Welcome to BaseLang Immersion!");
                            $message->to($user->email, $user->first_name);
                        });

                        if ($user->registered_inmersion) {
                            User::where("id", $user->id)->update(["registered_inmersion" => 0]);
                        }

                        //Email to Niall and Thomas
                        \Mail::send('emails.new_user_inmersion', ["user" => $user, "selecteds" => $selecteds, "teacher" => $teacher], function ($message) use ($user) {
                            $message->subject("New student - Immersion");
                            $message->bcc(['niall@baselang.com' => 'Niall', 'thomas.codetosuccess@gmail.com' => 'Thomas']);
                        });
                    }
                } catch (\Exception $e) {
                    Log::error('Cant send email: '.$e->getMessage());
                }

                User::where("id",$user->id)->update(["location_id"=>$location->id]);

                InmersionPayment::where("user_id",$user->id)->delete();

                return response()->json(['response'=>'success']);
            }

        }catch (\Exception $e){
            if(isset($result)){
                \Log::error('Error buy Inmersion: '.var_export($result->message,true));
            } else {
                \Log::error('Error buy Inmersion: '.var_export($e->getMessage(),true));
            }
            try {
                if (\App::environment('production')) {
                    //Email to Enrique
                    \Mail::send('emails.rejected_immersion_student', ["user" => $user], function ($message) use ($user) {
                        $message->subject("New rejected immersion student!");
                        $message->bcc(['niall@baselang.com' => 'Niall', 'enriquec@baselang.com' => 'Enrique']);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Cant send email: '.$e->getMessage());
            }
            return response()->json(['response'=>'error']);
        }

    }

    public function updateCardInmersion(Request $request) {
        $user = User::getCurrent();
        $refresh_chargebee= $user->refreshPaymentMethods();

        $selecteds = $request->input('selecteds');
        $location_id = $request->input('location_id');

        $location = Location::find($location_id);

        if(!$location) {
            Log::info("Location ".$location_id." does not exist");
            return redirect()->route("logout");
        }

        $refresh_chargebee = $user->refreshPaymentMethods();
        $user = User::find($user->id);
        $weeks = count($selecteds);
        Log::info("First verification of weeks: ".$weeks);

        $inmersion_start = null;
        $inmersion_end = null;
        $hour_format = null;
        $teacher_id = null;

        foreach($selecteds as $key => $selected) {
            $sel = explode(",",$selected);
            $inmersion_start = $sel[0];
            $inmersion_end = $sel[1];
            $hour_format = $sel[2];
            $teacher_id = $sel[3];
            
            $check_start_week = \DateTime::createFromFormat("Y-m-d", $inmersion_start)->sub(new \DateInterval("P7D"));
            $check_week_end = \DateTime::createFromFormat("Y-m-d", $inmersion_end)->sub(new \DateInterval("P7D"));

            $check_start_week_n = \DateTime::createFromFormat("Y-m-d", $inmersion_start)->add(new \DateInterval("P7D"));
            $check_week_end_n = \DateTime::createFromFormat("Y-m-d", $inmersion_end)->add(new \DateInterval("P7D"));

            $previous_immersion = BuyInmersion::where("teacher_id", $teacher_id)->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            $current_inmersion = BuyInmersion::where("teacher_id", $teacher_id)->where("inmersion_start", $inmersion_start)->where("inmersion_end", $inmersion_end)->where("hour_format", $hour_format)->first();

            $next_immersion = BuyInmersion::where("teacher_id", $teacher_id)->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            //Previous Inmersion - User active
            $previous_immersion_user = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $check_start_week->format("Y-m-d"))->where("inmersion_end", $check_week_end->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            //Current Inmersion - User active
            $current_inmersion_user = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $inmersion_start)->where("inmersion_end", $inmersion_end)->where("hour_format", $hour_format)->first();

            //Next Inmersion - User active
            $next_immersion_user = BuyInmersion::where("user_id", $user->id)->where("inmersion_start", $check_start_week_n->format("Y-m-d"))->where("inmersion_end", $check_week_end_n->format("Y-m-d"))->where("hour_format", $hour_format)->first();

            $teacher = User::find($teacher_id);
            $teacher_class = false;
            
            if($teacher) {
                $classes = $teacher->teacher_classes()->where("class_time",">=",$inmersion_start)->where("class_time","<=",$inmersion_end)->get();

                if(count($classes)>0 && $location) {

                    foreach($classes as $class) {
                        $local_time = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new \DateTimeZone($location->timezone))->format("H:i:s");

                        if($hour_format=="AM" && $local_time >= "08:00:00" && $local_time <= "12:30:00") {
                            $teacher_class = true;
                        }

                        if($hour_format=="PM" && $local_time >= "13:00:00" && $local_time <= "17:30:00") {
                            $teacher_class = true;
                        }
                    }

                }
            }

            $blocked_day=BlockDay::where("teacher_id",$teacher->id)->where("public_holiday","<>", 1)->where("blocking_day",">=",$inmersion_start)->where("blocking_day","<=",$inmersion_end)->first();

            if($previous_immersion || $current_inmersion || $next_immersion || $previous_immersion_user || $current_inmersion_user || $next_immersion_user || $teacher_class || $blocked_day) {
                unset($selecteds[$key]);
            }
        }

        $weeks = count($selecteds);
        Log::info("Second verification of weeks: ".$weeks);

        if($weeks==0) {
            return redirect()->route('inmersion',['location'=>$location->name])->withErrors(["The week(s) you have selected have already been filled by other students, try to schedule again!"]);
        }

        $total_price = $location->price;
        $amount = $total_price;

        try {

            if($user->referral_code)
			{
			    $discount_amount = $user->getDiscount($user->referral_code);
				if($discount_amount)
				{
					$amount = $amount - ($discount_amount/100);
				}
			}
            
            $second_payment_date = \DateTime::createFromFormat("Y-m-d", $inmersion_start)->sub(new \DateInterval("P7D"))->format("Y-m-d");
            
            $status = 0;
			if(!$user->timezone){
				$time_zone_from = "UTC";
			}
			else{
				$time_zone_from = $user->timezone;                    
			}
			$time_zone_to = "UTC";
			
			$inmersion_start_date = new \DateTime($inmersion_start, new \DateTimeZone($time_zone_from));
			$inmersion_start_date->setTimezone(new \DateTimeZone($time_zone_to));
			$inmersion_start_date_unix = $inmersion_start_date->getTimestamp();

            if($second_payment_date > gmdate('Y-m-d')) {
               
                $second_payment_date = new \DateTime($second_payment_date, new \DateTimeZone($time_zone_from));
                $second_payment_date->setTimezone(new \DateTimeZone($time_zone_to));
				try {
                    if($user->referral_code){
						$result = \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
						"planId" => "grammarless-medellin-600",
						"startDate" => $inmersion_start_date_unix,
						"couponIds" => [$user->referral_code]
						]); 					
					} else {
						$result = \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
						"planId" => "grammarless-medellin-600",
						"startDate" => $inmersion_start_date_unix
						]); 					
					}		
				} catch (\Exception $e){
					Log::error('Error payment method: '.var_export($e->getMessage(),true));
					return redirect()->route('inmersion',['location'=>$location->name])->withErrors(["Your payment was declined. We recommend trying to sign up again using a different credit card and/or phoning your bank to see why the payment was declined."]);
				}
				
            }else{
				try {
                    if($user->referral_code){
						$result = \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
						"planId" => "grammarless-medellin-1200",
						"startDate" => $inmersion_start_date_unix,
						"couponIds" => [$user->referral_code]
						]); 					
					} else {
						$result = \ChargeBee_Subscription::createForCustomer($user->chargebee_id,[
						"planId" => "grammarless-medellin-1200",
						"startDate" => $inmersion_start_date_unix
						]); 					
					}		
				} catch (\Exception $e){
					Log::error('Error payment method: '.var_export($e->getMessage(),true));
					return redirect()->route('inmersion',['location'=>$location->name])->withErrors(["Your payment was declined. We recommend trying to sign up again using a different credit card and/or phoning your bank to see why the payment was declined."]);
				}
                \Log::info("The total amount is charged because the start date is already greater than the one destined to make the second payment!");
                $status = 1;
            }
			
            \Log::info("Buy Inmersion for: ".$user->email." - Amount: $".$amount." - PMT: ".$user->payment_method_token);

            BuyInmersion::create(["user_id"=>$user->id, "teacher_id"=>$teacher_id, "total_price"=>$amount, "inmersion_start"=>$inmersion_start_date, "inmersion_end"=>$inmersion_end, "hour_format"=>$hour_format, "second_payment_date"=>$second_payment_date, "status"=>$status,"location_id"=>$location_id]);

            $inmersion = $user->inmersions_without_paying->sortByDesc("created_at")->first();

            if(!$inmersion) {
                $inmersion = $user->paid_inmersions->sortByDesc("created_at")->first();
            }

            try {
                if (\App::environment('production')) {
                    $teacher = User::find($teacher_id);
                    \Mail::send('emails.user_welcome_inmersion', ["location"=>$location,"user" => $user, "selecteds" => $selecteds, "teacher" => $teacher], function ($message) use ($user) {
                        $message->from('support@baselang.com', 'BaseLang');
                        $message->subject("Welcome to BaseLang Immersion!");
                        $message->to($user->email, $user->first_name);
                    });

                    session(["inmersion_password" => null]);

                    //Email to Niall and Thomas
                    \Mail::send('emails.new_user_inmersion', ["user" => $user, "selecteds" => $selecteds, "teacher" => $teacher], function ($message) use ($user) {
                        $message->subject("New student - Immersion");
                        $message->bcc(['niall@baselang.com' => 'Niall', 'thomas.codetosuccess@gmail.com' => 'Thomas']);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Cant send email: '.$e->getMessage());
            }

            User::where("id",$user->id)->update(["location_id"=>$location->id]);

            InmersionPayment::where("user_id",$user->id)->delete();

        } catch (\Exception $e){
            if(isset($result)){
                \Log::error('Error buy Inmersion1: '.var_export($result->message,true));
            } else {
                \Log::error('Error buy Inmersion2: '.var_export($e->getMessage(),true));
            }
            try {
                if (\App::environment('production')) {
                    //Email to Enrique
                    \Mail::send('emails.rejected_immersion_student', ["user" => $user], function ($message) use ($user) {
                        $message->subject("New rejected immersion student!");
                        $message->bcc(['niall@baselang.com' => 'Niall', 'enriquec@baselang.com' => 'Enrique']);
                    });
                }
            } catch (\Exception $e) {
                Log::error('Cant send email: '.$e->getMessage());
            }

            return redirect()->route('inmersion',['location'=>$location->name])->withErrors(["Error buy Inmersion, payment method not validated, try again!"]);
        }

        return view('inmersion.success', ["user"=>$user, "inmersion"=>$inmersion, "menu"=>"success", "location"=>$location]);
    }

    public function successfulInmersion(Request $request) {
        $user = User::getCurrent();

        $location_id = $request->input('location_id');
        $location = Location::find($location_id);
        
        $inmersion = $user->inmersions_without_paying->sortByDesc("created_at")->first();

        if(!$inmersion) {
            $inmersion = $user->paid_inmersions->sortByDesc("created_at")->first();
        }

        return view('inmersion.success', ["user"=>$user, "inmersion"=>$inmersion, "menu"=>"success", "location"=>$location]);
    }

    public function dashboardInmersion() {
        $user = User::getCurrent();
        \Log::info('Redirect to inmersion dashboard');
        return redirect()->route("dashboard");
    }

    public function getCityInformation() {
        $user = User::getCurrent();

        if($user->location_id) {
            $information_contents = InformationContents::where('location_id',$user->location_id)->where("type","city_info_medellin")->where("state",1)->orderBy("order","asc")->get();

            if($information_contents->count()>0){
                \Log::info('Go to City Information');
                return view("inmersion.city_information", ["breadcrumb"=>true, "menu_active"=>"city_information", "information_contents"=>$information_contents]);
            } else {
                return redirect()->route("classes_in_person_new");
            }
        }

        return redirect()->route("dashboard");
    }

    public function getInformation($info_slug) {
        $information_content = InformationContents::where("slug",$info_slug)->where("state",1)->first();

        if(!$information_content) {
            return redirect()->back()->withErrors(["Slug not existing"]);
        }

        $information_contents = InformationContents::where("information_content_id",$information_content->id)->where("state",1)->get();

        if(count($information_contents)==0) {
            return redirect()->back()->withErrors(["There is no content available for this level!"]);
        }

        return view("inmersion.get_city_information", ["breadcrumb"=>true, "menu_active"=>"city_information", "information_content"=>$information_content, "information_contents"=>$information_contents]);
    }

    public function getInformationContent($info_slug, $info_slug_content) {
        $information_content = InformationContents::where("slug",$info_slug)->where("state",1)->first();

        if(!$information_content) {
            return redirect()->back()->withErrors(["Slug not existing"]);
        }

        $content = InformationContents::where("slug",$info_slug_content)->where("state",1)->first();

        if(!$content) {
            return redirect()->back()->withErrors(["Slug not existing"]);
        }

        return view("inmersion.get_city_information_content", ["breadcrumb"=>true, "menu_active"=>"city_information", "information_content"=>$information_content, "content"=>$content]);
    }

    public function getAdvice() {
        $user = User::getCurrent();

        if($user->isInmersionStudent() || $user->isSchoolStudent()) {
            \Log::info('Go to Advice');
            return view("inmersion.advice", ["menu_active"=>"advice"]);
        }

        return redirect()->route("dashboard");
    }
}
