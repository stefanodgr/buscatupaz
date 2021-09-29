<?php

namespace App\Console\Commands;

use App\Models\Classes;
use Illuminate\Console\Command;

class CancelClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:cancelclasses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel next 12h Of classes';

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

        $time = 24;
        $type = false;
        $location = Null;
        $exclude_teachers = [6191,6756,6768,6753,6456,3340,3217,4621,757,3427,1004,5806];
        $limit_time = \DateTime::createFromFormat('Y-m-d H:i:s',gmdate('Y-m-d H:i:s'))->add(new \DateInterval('PT'.$time.'H'));


        $classes = Classes::where('class_time','>',gmdate('Y-m-d H:i:s'))->where('class_time','<',$limit_time->format('Y-m-d H:i:s'));


        if($type!==false){
            $classes = $classes->where('type',$type);
        }

        if($location!==false){
            $classes = $classes->where('location_id',$location);
        }


        if(count($exclude_teachers)>0){
            $classes = $classes->whereNotIn('teacher_id',$exclude_teachers);
        }

        $classes = $classes->get();

        foreach($classes as $class){

            try {
                if($class->location_id){
                    $this->info("Class with location ID: ".$class->id." Location: ".$class->location_id);
                    continue;
                }
                $this->info("Removing class ID: ".$class->id);
                $this->info("Removing class Teacher: ".'Name: '.$class->teacher->first_name.' Last Name: '.$class->teacher->last_name.' Email: '.$class->teacher->email);
                $this->info("Removing class Student: ".'Name: '.$class->student->first_name.' Last Name: '.$class->student->last_name.' Email: '.$class->student->email);
                $class->delete();
                $class->removeGoogle();
                $class->removeZoom();
            } catch (\Exception $e){
                $this->info("Error ".$e->getMessage());
            }
        }


        $this->info("Command Ends");
        return true;



    }
}
