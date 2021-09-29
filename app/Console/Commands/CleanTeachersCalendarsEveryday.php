<?php

namespace App\Console\Commands;

use App\Models\UserCalendar;
use App\Models\UserFreeDays;
use App\User;
use Illuminate\Console\Command;
use DateTime;

class CleanTeachersCalendarsEveryday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean_teachers_calendars_everyday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Users Referral';

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

        $now = new DateTime();
        //dd($now->getTimezone());
        $now->setTimezone(new \DateTimeZone('America/Bogota'));
        $weekdayn =  $now->format('N');

        if($weekdayn==1) {
            //Monday, delete Sunday
            $weekdaytodelete=7;
        } else {
            $weekdaytodelete=$weekdayn-1;

        }

        UserCalendar::where("day","=",$weekdaytodelete)->where("day",7)->delete();

        $msg = "Cleaning Calendar for day: $weekdaytodelete\n";
        $this->info($msg);
        \Log::info($msg);

        die('sisi');

    }
}
