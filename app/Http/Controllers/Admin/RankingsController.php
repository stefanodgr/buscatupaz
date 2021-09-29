<?php

namespace App\Http\Controllers\Admin;

use App\Models\Location;
use App\Models\Role;
use App\Models\Statistics;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RankingsController extends Controller
{
	public function teacherStatistics(){
		$locations = Location::orderBy("name", "ASC")->get();
		return view("admin.rankings.teacher_statistics",["menu_active"=>"rankings", "locations"=>$locations]);
	}

    public function getRankings(){
	 	$locations = Location::orderBy("name", "ASC")->get();
        return view("admin.rankings.rankings",["menu_active"=>"rankings", "locations"=>$locations]);
    }

    public function getTeachersList(){
		$teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();

		//Current month
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->current_month_five = $ratings[4];
	 		$teacher->current_month_four = $ratings[3];
	 		$teacher->current_month_three = $ratings[2];
	 		$teacher->current_month_two = $ratings[1];
	 		$teacher->current_month_one = $ratings[0];
	 	}

		//A month before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P1M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P1M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->first_month_five = $ratings[4];
	 		$teacher->first_month_four = $ratings[3];
	 		$teacher->first_month_three = $ratings[2];
	 		$teacher->first_month_two = $ratings[1];
	 		$teacher->first_month_one = $ratings[0];
	 	}

		//Two months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P2M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P2M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->second_month_five = $ratings[4];
	 		$teacher->second_month_four = $ratings[3];
	 		$teacher->second_month_three = $ratings[2];
	 		$teacher->second_month_two = $ratings[1];
	 		$teacher->second_month_one = $ratings[0];
	 	}

		//Three months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P3M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P3M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->third_month_five = $ratings[4];
	 		$teacher->third_month_four = $ratings[3];
	 		$teacher->third_month_three = $ratings[2];
	 		$teacher->third_month_two = $ratings[1];
	 		$teacher->third_month_one = $ratings[0];
	 	}

		//Four months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P4M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P4M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->fourth_month_five = $ratings[4];
	 		$teacher->fourth_month_four = $ratings[3];
	 		$teacher->fourth_month_three = $ratings[2];
	 		$teacher->fourth_month_two = $ratings[1];
	 		$teacher->fourth_month_one = $ratings[0];
	 	}

		//Five months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P5M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P5M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->fifth_month_five = $ratings[4];
	 		$teacher->fifth_month_four = $ratings[3];
	 		$teacher->fifth_month_three = $ratings[2];
	 		$teacher->fifth_month_two = $ratings[1];
	 		$teacher->fifth_month_one = $ratings[0];
	 	}

        return view("admin.rankings.includes.teachers",["teachers"=>$teachers]);
    }

    public function getTeachersFilterList($specific_rating, $location_id){

    	$teachers = null;
    	
    	if($location_id=="all") {
			$teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();
    	}elseif($location_id=="none") {
			$teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->where("location_id",null)->orderBy("first_name","asc")->get();
    	}else{
            $teachers = collect();
            $verify_teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();

            foreach($verify_teachers as $teacher) {
                if($teacher->hasLocation($location->id)) {
                    $teachers->push($teacher);
                }
            }
    	}

		//Current month
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->current_month_five = $ratings[4];
	 		$teacher->current_month_four = $ratings[3];
	 		$teacher->current_month_three = $ratings[2];
	 		$teacher->current_month_two = $ratings[1];
	 		$teacher->current_month_one = $ratings[0];
	 	}

		//A month before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P1M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P1M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->first_month_five = $ratings[4];
	 		$teacher->first_month_four = $ratings[3];
	 		$teacher->first_month_three = $ratings[2];
	 		$teacher->first_month_two = $ratings[1];
	 		$teacher->first_month_one = $ratings[0];
	 	}

		//Two months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P2M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P2M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->second_month_five = $ratings[4];
	 		$teacher->second_month_four = $ratings[3];
	 		$teacher->second_month_three = $ratings[2];
	 		$teacher->second_month_two = $ratings[1];
	 		$teacher->second_month_one = $ratings[0];
	 	}

		//Three months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P3M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P3M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->third_month_five = $ratings[4];
	 		$teacher->third_month_four = $ratings[3];
	 		$teacher->third_month_three = $ratings[2];
	 		$teacher->third_month_two = $ratings[1];
	 		$teacher->third_month_one = $ratings[0];
	 	}

		//Four months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P4M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P4M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->fourth_month_five = $ratings[4];
	 		$teacher->fourth_month_four = $ratings[3];
	 		$teacher->fourth_month_three = $ratings[2];
	 		$teacher->fourth_month_two = $ratings[1];
	 		$teacher->fourth_month_one = $ratings[0];
	 	}

		//Five months before
	 	$first_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P5M"));
	 	$second_date = \DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->sub(new \DateInterval("P5M"));
	 	$first_day = $first_date->modify('first day of '.$first_date->format("F").' '.$first_date->format("Y"));
	 	$second_day = $second_date->modify('last day of '.$second_date->format("F").' '.$second_date->format("Y"));

	 	foreach($teachers as $teacher){
	 		$statistics = Statistics::where("type","Evaluation_teacher")->where("data_x",$teacher->id)->where("created_at",">=",$first_day->format("Y-m-d 00:00:00"))->where("created_at","<=",$second_day->format("Y-m-d 11:59:59"))->orderBy('created_at','desc')->get();
	 		
	 		$users = [];
	 		$ratings = [0,0,0,0,0];

	 		foreach($statistics as $statistic){
                if(!isset($users[$statistic->user_id])){
                    $users[$statistic->user_id] = $statistic->data_y;
                    $ratings[($statistic->data_y)-1]++;
                }
	 		}
	 		$teacher->fifth_month_five = $ratings[4];
	 		$teacher->fifth_month_four = $ratings[3];
	 		$teacher->fifth_month_three = $ratings[2];
	 		$teacher->fifth_month_two = $ratings[1];
	 		$teacher->fifth_month_one = $ratings[0];
	 	}

		if($specific_rating!="all"){
			$teachers_list=collect();

			foreach($teachers as $teacher){
				if($teacher->getEvaluatedStars($specific_rating)!=0){
					$teacher->evaluated_stars=$teacher->getEvaluatedStars($specific_rating);
					$teachers_list->push($teacher);
				}
			}

			$teachers_list=$teachers_list->sortByDesc('evaluated_stars');

	        return view("admin.rankings.includes.teachers",["teachers"=>$teachers_list]);
		}

		return view("admin.rankings.includes.teachers",["teachers"=>$teachers]);
    }

    public function csvRankings()
    {
        $teachers = Role::where('name','teacher')->first()->users()->where("activated",1)->orderBy("first_name","asc")->get();

        foreach($teachers as $teacher){
        	$teacher->average_rating = $teacher->getEvaluated();
        	$teacher->five_stars = $teacher->getEvaluatedStars(5);
        	$teacher->four_stars = $teacher->getEvaluatedStars(4);
        	$teacher->three_stars = $teacher->getEvaluatedStars(3);
        	$teacher->two_stars = $teacher->getEvaluatedStars(2);
        	$teacher->one_star = $teacher->getEvaluatedStars(1);

            $location = null;
            if($teacher->location_id) {
                $location = Location::find($teacher->location_id);
                if($location) {
                    $location = ucwords(strtolower($location->name));
                }else {
                    $location = "Undefined";
                }
            }else {
                $location = "Online";
            }

            $teacher->location = $location;
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($teachers, ['first_name', 'last_name', 'email', 'average_rating', 'five_stars', 'four_stars', 'three_stars', 'two_stars', 'one_star', 'location'])->download();
    }

}
