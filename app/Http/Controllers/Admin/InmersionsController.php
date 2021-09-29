<?php

namespace App\Http\Controllers\Admin;

use App\Models\BuyInmersion;
use App\Models\Location;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class InmersionsController extends Controller
{
    public function __construct() {
        View::share('menu_active', "inmersions");
    }

    public function getIndex() {
    	$locations = Location::orderBy("name", "ASC")->get();

    	if(count($locations)==0) {
    		Log::info("There are no registered locations!");
    		return redirect()->route('dashboard');
    	}

    	return view('admin.inmersions.list', ["locations"=>$locations]);
    }

    public function getCalendar($location_id) {
    	$location = Location::find($location_id);

    	$teachers = $location->teachers()->orderBy("first_name","ASC")->get();

        $calendars = [];
        $count = 0;

        if(count($teachers)==0) {
            Log::info("There are no teachers available");
        }else {
	        Log::info("Teachers: ".count($teachers));
	        Log::info("Calendar of Inmersion");

	        $start_week = \DateTime::createFromFormat("U",strtotime('monday this week'),new \DateTimeZone("UTC"))->add(new \DateInterval("P7D"));

	        $final_week = clone $start_week;
	        $final_week = $final_week->add(new \DateInterval("P24M"));

	        Log::info("Start week: ".$start_week->format("Y-m-d"));
	        Log::info("Final week: ".$final_week->format("Y-m-d"));

	        while($start_week->format("Y-m-d") < $final_week->format("Y-m-d")) {
	            $week_end = clone $start_week;
            	$week_end = $week_end->add(new \DateInterval("P26D"));

	            if(!isset($calendars[$start_week->format("Y-m-d")])) {
	                $calendars[$start_week->format("Y-m-d")] = collect();
	                $count++;
	            }

	            $inmersions = BuyInmersion::where("location_id",$location_id)->where("inmersion_start", $start_week->format("Y-m-d"))->where("inmersion_end", $week_end->format("Y-m-d"))->get();

	            if(count($inmersions) > 0) {
	            	foreach($inmersions as $inmersion) {
	            		$calendars[$start_week->format("Y-m-d")]->push(["inmersion_id"=>$inmersion->id, "count"=>$count]);
	            	}
	            }

	            $start_week = $start_week->add(new \DateInterval("P7D"));
	        }
        }

        $final_calendars=[];
        foreach($calendars as $date => $calendar) {

            foreach($calendar as $key => $cal) {

                if(!isset($final_calendars[$key])) {
                    $final_calendars[$key]=collect();

                    for($i=1; $i<=$count ; $i++) {

                        if($i==$cal["count"]) {
                        	$inmersion = BuyInmersion::find($cal["inmersion_id"]);
                            $final_calendars[$key]->push(["inmersion_id"=>$cal["inmersion_id"], "count"=>$cal["count"], "inmersion"=>$inmersion]);
                        }else {
                            $final_calendars[$key]->push(["inmersion_id"=>null, "count"=>$i, "inmersion"=>null]);
                        }
                    }
                }else{

                    for($i=1; $i<=$count ; $i++) {

                        if($i==$cal["count"]) {
                        	$inmersion = BuyInmersion::find($cal["inmersion_id"]);
                            $final_calendars[$key][$i-1] = ["inmersion_id"=>$cal["inmersion_id"], "count"=>$cal["count"], "inmersion"=>$inmersion];
                        }
                    }
                }
            }
        }

        if(count($final_calendars)==0) {
        	return view("admin.inmersions.includes.not_inmersions");
        }

    	return view("admin.inmersions.includes.calendar", ["calendars"=>$calendars, "final_calendars"=>$final_calendars]);
    }
}
