<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Subscription;

class EditSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:edit_subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit Subscriptions';

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
        $students = Role::where('name','student')->first()->users()->where("activated",1)->orderBy("id","ASC")->get();
        $this->info("Students to verify: ".count($students)."\n");

        foreach($students as $student) {
            $subscriptions = $student->subscriptions;
            if($subscriptions && count($subscriptions) > 0) {
                foreach($subscriptions as $subscription) {
                    if($subscription->status== "future" || $subscription->status=="cancelled") {
                        $starts_at = new \DateTime($subscription->starts_at);
                        $ends_at = new \DateTime($subscription->ends_at);
                        $diff = $starts_at->diff($ends_at);   
                        $days = $diff->days;
                        if($days < 30) {
                            $this->info("Student: ".$student->id." - Status: ".$subscription->status." - Starts at: ".$subscription->starts_at." - Ends at: ".$subscription->ends_at." - Difference of days: ".$days);
                            Subscription::where("id",$subscription->id)->update(["ends_at" => \DateTime::createFromFormat("Y-m-d",$subscription->starts_at)->add(new \DateInterval("P30D"))]);
                        }
                    }
                }
            }
        }

        $this->info("\nEnd of the verification!");
    }
}
