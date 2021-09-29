<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class RestartGoogleCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:restartgooglecalendar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart Google Calendar';

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
        $users = User::all();

        $this->info("\nUsers:\n");

        foreach ($users as $user) 
        {
            $user->update(["google_token"=>null,"refresh_google_token" => null]);
            $this->info($user->email);
        }

        $this->info("\nTotal users: ".count($users)."\n");
    }
}
