<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdataDataBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updataDBUsers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Users Update';

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
        $users = User::where('cancel_at','>=',gmdate("Y-m-d"))->get();
        foreach($users as $user){
            if($user->subscribed()){
                continue;
            } else {
                Subscription::where("user_id",$user->id)->delete();
                Subscription::create(["status"=>"cancelled","user_id"=>$user->id,"subscription_id"=>"BaseLang","plan_name"=>$user->last_unlimited_subscription,"starts_at"=>gmdate("Y-m-d"),"ends_at"=>$user->cancel_at]);
            }
        }
        $this->info('Migration Completed');
        return true;
    }
}
