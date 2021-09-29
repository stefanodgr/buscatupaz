<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Subscription;
use App\User;
use Illuminate\Support\Facades\Log;

class RemoveSubscriptionsExtras extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:remove_subs_extras';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Subscriptions Extras';

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
        $students = Role::where('name','student')->first()->users()->where("activated",1)->orderBy("email","ASC")->get();

        $this->info("Students to verify: ".count($students)."\n");
        foreach($students as $student) {
            $count_subscription = count($student->subscriptions);
            $subscriptions = $student->subscriptions->sortBy("ends_at");

            if($count_subscription > 1) {
                $this->info("Student: ".$student->email." - Subscriptions: ".$count_subscription);
                $active = [];
                $pending = [];
                $cancelled = [];

                foreach($subscriptions as $subscription) {
                    if($subscription->status=="active") {
                        $this->info("Active - Ends at: ".$subscription->ends_at);
                        $active[] = $subscription->id;
                    }elseif($subscription->status=="future") {
                        $this->info("Pending - Ends at: ".$subscription->ends_at);
                        $pending[] = $subscription->id;
                    }elseif($subscription->status=="cancelled") {
                        $this->info("cancelled - Ends at: ".$subscription->ends_at);
                        $cancelled[] = $subscription->id;
                    }
                }

                if(count($active)>1) {

                    foreach($cancelled as $cancel) {
                        $subs = Subscription::find($cancel);
                        $this->info("cancelled to delete: ".$student->email." - Ends at: ".$subs->ends_at);
                        Subscription::where("id",$cancel)->delete();
                    } 

                }else {

                    if(count($cancelled)>1) {
                        $count_canceled = 0;
                        foreach($cancelled as $cancel) {
                            $subs = Subscription::find($cancel);
                            $count_canceled++;
                            $this->info("cancelled to delete: ".$student->email." - Ends at: ".$subs->ends_at);
                            Subscription::where("id",$cancel)->delete();
                            if((count($cancelled)-1)==$count_canceled) {
                                break;
                            }
                        }
                    }

                    if(count($cancelled)>=1 && (count($pending)>=1 || count($active)==1)) {
                        foreach($cancelled as $cancel) {
                            $subs = Subscription::find($cancel);
                            if($subs) {
                                $this->info("cancelled to delete: ".$student->email." - Ends at: ".$subs->ends_at);
                                Subscription::where("id",$cancel)->delete();
                            }
                        } 
                    }

                }

                $this->info("\n");
            }
        }

        $this->info("\nEnd of the verification!");
    }
}
