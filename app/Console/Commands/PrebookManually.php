<?php

namespace App\Console\Commands;

use App\Models\BuyPrebook;
use App\Models\Classes;
use App\Models\Prebook;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PrebookManually extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:prebook_manual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Prebook Manually With custom date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->ask("Enter Date Y-m-d Format");
        $buy_prebooks = BuyPrebook::where("status",1)->get();
        foreach($buy_prebooks as $buy_prebook) {
            $limit=\DateTime::createFromFormat("Y-m-d",$buy_prebook->activation_date)->add(new \DateInterval("P1Y"));

            if(!$limit){
                Log::info("Error loading limit for: ". $buy_prebook->id. " Activation Date: ". var_export($buy_prebook->activation_date,true). " type: " .var_export($buy_prebook->type,true)." limit: ".var_export($limit,true));
            }

            if($limit && $date>=$limit->format("Y-m-d")) {
                Log::info("Remove prebook for user: ". $buy_prebook->student->email . " Activation Date: ". var_export($buy_prebook->activation_date,true). " type: " .var_export($buy_prebook->type,true)." limit: ".var_export($limit,true));
                BuyPrebook::where("id",$buy_prebook->id)->update(["status"=>0]);
                Prebook::where("user_id",$buy_prebook->student->id)->delete();
            }
        }

        $current_day=\DateTime::createFromFormat("Y-m-d H:i:s",$date." 14:00:00");
        $day=$current_day->format("N");
        $prebooks = Prebook::where("day",$day)->get();
        Log::info("Getting Prebooks for today: ".$date." with N: ".$day." count: ".$prebooks->count());

        foreach($prebooks as $prebook) {
            Log::info("Do prebook for user: ". $prebook->student->email." with id: ".$prebook->id);
            $first_date=\DateTime::createFromFormat("Y-m-d H:i:s",$date." ".$prebook->hour)->add(new \DateInterval("P7D"));
            $second_date=\DateTime::createFromFormat("Y-m-d H:i:s",$date." ".$prebook->hour)->add(new \DateInterval("P14D"));

            $user=$prebook->student;
            $hourly=false;
            if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly") {
                $hourly = true;
            }

            if($user && $user->activated && $user->subscribed()) {
                $classes=collect();
                $first_class=Classes::where("user_id",$user->id)->where("teacher_id",$prebook->teacher->id)->where("class_time",$first_date)->first();
                $second_class=Classes::where("user_id",$user->id)->where("teacher_id",$prebook->teacher->id)->where("class_time",$second_date)->first();


                if(!$first_class){
                    if($hourly) {
                        if($user->credits==0) {
                            Log::info("No credits for Book prebook");
                            continue;
                        }

                        Log::info("User Credits for prebook: ".$user->credits);
                        $user->credits--;
                        Log::info("Credits -1 ".$user->credits);
                        User::where("id",$user->id)->update(["credits"=>$user->credits]);

                    }

                    $class = new Classes();
                    $class->user_id=$user->id;
                    $class->teacher_id=$prebook->teacher->id;
                    $class->class_time=$first_date->format("Y-m-d H:i:s");
                    $class->type=$prebook->type;
                    $class->save();
                    Log::info("Save class prebook #1: ".$class->id." - teacher_id: ".$class->teacher_id." - user_id: ".$class->user_id);
                    $class->createZoom($prebook->teacher);
                    $classes->push($class);
                } else {
                    Log::info("Class booked for prebook #1: ".$first_class->id." - teacher_id: ".$first_class->teacher_id." - user_id: ".$first_class->user_id);
                    Log::info("Cannot Book prebook #1: ".$first_date->format("Y-m-d H:i:s")." - teacher_id: ".$prebook->teacher->id." - user_id: ".$user->email);
                }

                if(!$second_class){

                    if($hourly) {
                        if($user->credits==0) {
                            Log::info("No credits for Book prebook");
                            continue;
                        }

                        Log::info("User Credits for prebook: ".$user->credits);
                        $user->credits--;
                        Log::info("Credits -1 ".$user->credits);
                        User::where("id",$user->id)->update(["credits"=>$user->credits]);

                    }


                    $class = new Classes();
                    $class->user_id=$user->id;
                    $class->teacher_id=$prebook->teacher->id;
                    $class->class_time=$second_date->format("Y-m-d H:i:s");
                    $class->type=$prebook->type;
                    $class->save();
                    Log::info("Save class prebook #2: ".$class->id." - teacher_id: ".$class->teacher_id." - user_id: ".$class->user_id);
                    $class->createZoom($prebook->teacher);
                    $classes->push($class);
                } else {
                    Log::info("Class booked for prebook #2: ".$second_class->id." - teacher_id: ".$second_class->teacher_id." - user_id: ".$second_class->user_id);
                    Log::info("Cannot Book prebook #2: ".$second_date->format("Y-m-d H:i:s")." - teacher_id: ".$prebook->teacher->id." - user_id: ".$user->email);
                }

                if(count($classes)>0) {
                    try {
                        if (\App::environment('production')) {
                            \Mail::send('emails.student_class_confirmed_prebook', ["user" => $user, "classes" => $classes], function ($message) use ($user) {
                                $message->subject(__('Class Confirmed'));
                                $message->to($user->email, $user->first_name);
                            });
                        }

                        if (\App::environment('production')) {
                            $teacher = $prebook->teacher;
                            \Mail::send('emails.teacher_class_confirmed_prebook', ["user" => $user, "teacher" => $teacher, "classes" => $classes], function ($message) use ($teacher) {
                                $message->subject(__('Class Confirmed'));
                                $message->to($teacher->email, $teacher->first_name);
                            });
                        }
                    } catch (\Exception $e) {
                        Log::error('Cant send email: '.$e->getMessage());
                    }
                } else {
                    Log::info("No prebook classes for user: ".var_export($prebook->user_id,true)." with prebook: ".$prebook->id." type: ".$prebook->type);
                }
            } else {
                Log::info("No prebook saved for user: ".var_export($prebook->student->email,true).", user without subscription, prebook: ".$prebook->id." type: ".$prebook->type);
            }
        }
    }
}
