<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Statistics;
use App\User;
use Illuminate\Console\Command;

class MigrateTeachersFavoritesToStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:teachers_favorites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the teachers favorites to statistics';

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
        $students = Role::where('name','student')->first()->users()->where("activated",1)->where("favorite_teacher","<>",0)->get();

        //Favorites teacher
        $teachers=[];

        $this->info("\nStarting migration!");
        
        foreach($students as $student){
            $teacher=User::where("id",$student->favorite_teacher)->first();
             if($teacher){
                Statistics::create(["user_id"=>$student->id,"type"=>"Favorite_teacher","data_x"=>$teacher->id,"data_y"=>"favorite_teacher"]); 
            }
        }

        $this->info("\nMigration completed!");
    }
}
