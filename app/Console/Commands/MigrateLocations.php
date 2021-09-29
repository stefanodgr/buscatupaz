<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Location;

class MigrateLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate_locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Locations';

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
        $teachers=Role::where('name','teacher')->first()->users()->where('activated',1)->where('location_id','<>',null)->orderBy('id','asc')->get();
        $this->info('Teachers to migrate: '.count($teachers)."\n");

        foreach($teachers as $teacher){
            foreach($teacher->teacher_locations as $location) {
                $teacher->teacher_locations()->detach($location->id);
            }
            $location=Location::find($teacher->location_id);
            $teacher->update(['location_id'=>null]);
            if($location) {
                $teacher->teacher_locations()->attach($location->id);
            }
            $this->info('Teacher: '.$teacher->email);
        }

        $this->info("\nEnd of the verification!");
    }
}
