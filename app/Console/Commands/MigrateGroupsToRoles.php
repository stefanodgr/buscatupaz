<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\User;
use Chargebee\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateGroupsToRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:groupstoroles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the users in groups to roles';

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
        $groups=DB::table("groups")->get();

        //Migrate User to Roles
        foreach($groups as $group){
            $deleteacher=false;

            if($group->name=="Admin"){
                $role_name="admin";
            } elseif($group->name=="Students"){
                $role_name="student";
            } elseif($group->name=="Teachers"){
                $role_name="teacher";
            } elseif($group->name=="Dele Teachers"){
                $role_name="teacher";
                $deleteacher=true;
            } elseif($group->name=="Coordinators"){
                $role_name="coordinator";
            } else {
                continue;
            }

            $role = Role::where("name",$role_name)->first();
            if(!$role){
                $role = new Role();
                $role->name=$role_name;
                $role->display_name=$group->name;
                $role->save();
            } elseif(!$deleteacher){
                continue;
            }
            //old group id -> new role id

            $user_groups=DB::table("users_groups")->where("group_id",$group->id)->get();
            foreach($user_groups as $user_group){
                $current_user_group=DB::table("role_user")->where("user_id",$user_group->user_id)->where("role_id",$role->id)->first();
                if(!$current_user_group){
                    DB::table("role_user")->insert(["user_id"=>$user_group->user_id,"role_id"=>$role->id]);
                }

                if($deleteacher){
                    User::where("id",$user_group->user_id)->update(["is_deleteacher"=>1]);
                }
            }

            //permissions,activation_code
        }

        //Copy Homeworks
        $homeworks=DB::table("homeworks")->get();

        foreach($homeworks as $homework){
            $user_lesson=DB::table("users_lessons")->where("user_id",$homework->userid)->where("lesson_id",$homework->lessonid)->first();
            if($user_lesson){
                DB::table("users_lessons")->where("user_id",$homework->userid)->where("lesson_id",$homework->lessonid)->update(["homework"=>$homework->homeworktext]);
            } else {
                DB::table("users_lessons")->insert(["completed"=>0,"homework"=>$homework->homeworktext,"user_id"=>$homework->userid,"lesson_id"=>$homework->lessonid]);
            }
        }

        //Copy PDF files
        $lessons=DB::table("lessons")->get();

        foreach($lessons as $lesson){
            //Copy Options to column

            $lesson->options=json_decode($lesson->options);
            if(!isset($lesson->options)){
                $lesson->options= new \stdClass();
            };
            if(!isset($lesson->options->homework_audio)){
                $lesson->options->homework_audio="";
            }
            if(!isset($lesson->options->homework_text)){
                $lesson->options->homework_text="";
            }
            if(!isset($lesson->options->externalurl)){
                $lesson->options->externalurl="";
            }

            DB::table("lessons")->where("id",$lesson->id)->update(["homework_audio"=>$lesson->options->homework_audio,"homework_text"=>$lesson->options->homework_text,"externalurl"=>$lesson->options->externalurl]);

            $fileLesson=Storage::disk("uploads")->exists('assets/lessons/pdf/'.$lesson->slug.'.pdf');

            if($fileLesson){
                $newFileExist=Storage::disk("uploads")->exists('assets/lessons/pdf/'.$lesson->id.'.pdf');
                if($newFileExist){
                    Storage::disk("uploads")->delete('assets/lessons/pdf/'.$lesson->id.'.pdf');
                }
                Storage::disk("uploads")->move('assets/lessons/pdf/'.$lesson->slug.'.pdf', 'assets/lessons/pdf/'.$lesson->id.'.pdf');
            }
            //$lesson->slug;
        }

        //Create Teacher Calendar
        $teachers = Role::where('name','teacher')->first()->users()->get();
        foreach($teachers as $teacher){
            if($teacher->options){
                $options=json_decode($teacher->options);
                if($options && isset($options->availablehours)){
                    foreach($options->availablehours as $k=>$availablehour){

                        foreach($availablehour->from as $j=>$hour){
                            if(!DB::table("users_calendar")->where("user_id",$teacher->id)->where("from",$hour)->where("till",$availablehour->till[$j])->where("day",$k+1)->first()){
                                DB::table("users_calendar")->insert(["user_id"=>$teacher->id,"from"=>$hour,"till"=>$availablehour->till[$j],"day"=>$k+1]);
                            };

                        };


                    };
                }
            };
        };

        $teachers = Role::where('name','teacher')->first()->users()->get();
        foreach($teachers as $teacher) {
            if ($teacher->options) {
                $options = json_decode($teacher->options);
                if ($options && isset($options->youtubeurl)) {
                    DB::table("users")->where("id",$teacher->id)->update(["youtube_url"=>$options->youtubeurl]);
                }
            }
        }

        $students = Role::where('name','student')->first()->users()->get();
        foreach($students as $student) {
            if ($student->options) {
                $options = json_decode($student->options);
                if ($options && isset($options->progressurl)) {
                    DB::table("users")->where("id",$student->id)->update(["real_sheet"=>$options->progressurl]);
                }
                if ($options && isset($options->deleprogressurl)) {
                    DB::table("users")->where("id",$student->id)->update(["dele_sheet"=>$options->deleprogressurl]);
                }

            }
        }


        DB::table('users_calendar')->where('from', '00:00:00')->where('till', '00:00:00')->delete();

        $this->info('Migration Completed');
        return true;
    }
}
