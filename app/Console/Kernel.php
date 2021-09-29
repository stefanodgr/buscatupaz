<?php

namespace App\Console;

use App\Console\Commands\AddDays;
use App\Console\Commands\CleanTeachersCalendarsEveryday;
use App\Console\Commands\AddHoursToClasses;
use App\Console\Commands\CancelClasses;
use App\Console\Commands\CheckRealWorld;
use App\Console\Commands\CreateSubscriptionTrial;
use App\Console\Commands\EditSubscriptions;
use App\Console\Commands\FixSubscriptions;
use App\Console\Commands\ManualCharges;
use App\Console\Commands\MigrateTeachersFavoritesToStatistics;
use App\Console\Commands\MigrateGroupsToRoles;
use App\Console\Commands\MigrateLocations;
use App\Console\Commands\MigratePDF;
use App\Console\Commands\MigrateUserEvaluationToStatistics;
use App\Console\Commands\PrebookManually;
use App\Console\Commands\RemoveSubscriptionsExtras;
use App\Console\Commands\ResizeTeachersPhotos;
use App\Console\Commands\RestartGoogleCalendar;
use App\Console\Commands\UndoSubscriptions;
use App\Console\Commands\UpdataDataBase;
use App\Console\Commands\UndoCancellations;
use App\Console\Commands\UserReferral;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AddHoursToClasses::class,
        CreateSubscriptionTrial::class,
        MigrateTeachersFavoritesToStatistics::class,
        MigrateGroupsToRoles::class,
        UpdataDataBase::class,
        MigratePDF::class,
        MigrateUserEvaluationToStatistics::class,
        ResizeTeachersPhotos::class,
        RestartGoogleCalendar::class,
        UserReferral::class,
        PrebookManually::class,
        RemoveSubscriptionsExtras::class,
        EditSubscriptions::class,
        UndoCancellations::class,
        AddDays::class,
        UndoSubscriptions::class,
        CancelClasses::class,
        CheckRealWorld::class,
        MigrateLocations::class,
        FixSubscriptions::class,
        ManualCharges::class,
        CleanTeachersCalendarsEveryday::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');

    }
}
