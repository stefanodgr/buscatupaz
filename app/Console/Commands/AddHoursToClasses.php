<?php

namespace App\Console\Commands;

use App\Models\Classes;
use App\User;
use Illuminate\Console\Command;

class AddHoursToClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add_hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add hours to classes';

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
        $email = $this->ask("Enter the student's email");
        $user = User::where("email",$email)->first();

        if($user) {
            $datetime = $this->ask("Enter the date of the class in format Y-m-d");
            $classes=Classes::where("user_id",$user->id)->where("class_time",">=",$datetime)->orderBy("class_time","desc")->get();
            if($classes) {
                $this->info("Classes to edit: ".count($classes));
                foreach($classes as $class) {
                    $this->info("Class: ".$class->id." - Class time: ".$class->class_time);
                }
                $hours_number = $this->ask("Enter the number of hours to add or subtract");
                foreach($classes as $class) {
                    if ($hours_number > 0) {
                        $new_datetime = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->add(new \DateInterval("PT".$hours_number."H"));

                        $check_class = Classes::where("teacher_id",$class->teacher_id)->where("class_time",$new_datetime->format("Y-m-d H:i:s"))->first();
                        if(!$check_class) {
                            $this->info("Add: ".$new_datetime->format("Y-m-d H:i:s"));
                            Classes::where("id",$class->id)->update(["class_time" => $new_datetime->format("Y-m-d H:i:s")]);
                        }else{
                            $this->info("A class already exists on ".$new_datetime->format("Y-m-d H:i:s"));
                        }
                    }elseif($hours_number < 0) {
                        $new_datetime = \DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->sub(new \DateInterval("PT".($hours_number*-1)."H"));
                        
                        $check_class = Classes::where("teacher_id",$class->teacher_id)->where("class_time",$new_datetime->format("Y-m-d H:i:s"))->first();
                        if(!$check_class) {
                            $this->info("Sub: ".$new_datetime->format("Y-m-d H:i:s"));
                            Classes::where("id",$class->id)->update(["class_time" => $new_datetime->format("Y-m-d H:i:s")]);
                        }else{
                            $this->info("A class already exists on ".$new_datetime->format("Y-m-d H:i:s"));
                        }
                    }
                }
            }else {
                $this->info("There is no classes on ".$datetime." for ".$user->email);
            }
        }else {
            $this->info("User ".$email." does not exist");
        }
    }
}
