<?php

namespace App\Console\Commands;

use App\Models\UserFreeDays;
use App\User;
use Illuminate\Console\Command;

class UserReferral extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:users_referral';

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
        $users=User::all();

        $this->info("USERS REFERRAL:\n");

        foreach($users as $user){
            if(!$user->referral_email){continue;}

            $user_referral=User::where("email",$user->referral_email)->first();

            if($user_referral){
                $user_free_days=UserFreeDays::where("user_id",$user_referral->id)->where("referred_id",$user->id)->first();
                
                if(!$user_free_days){
                    $this->info("User: ".$user->email." / ".$user->created_at);
                    $this->info("User Referral: ".$user_referral->email." / ".$user_referral->created_at."\n");
                }
            }
        }
    }
}
