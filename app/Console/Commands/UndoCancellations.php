<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Subscription;
use App\Models\UserCancellation;
use Illuminate\Console\Command;

class UndoCancellations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:undo_cancellations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Undo cancellations';

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
        $students=Role::where('name','student')->first()->users()->where("activated",1)->orderBy("email","ASC")->get();

        $this->info("Students to verify: ".count($students)."\n");
        $this->info("Students to undo cancellations");
        foreach($students as $student) {
            $subscriptions=$student->subscriptions->where("status","cancelled")->sortBy("ends_at");
            foreach($subscriptions as $subscription) {
                if($subscription->ends_at > gmdate("Y-m-d")) {
                    $user_cancellations=UserCancellation::where("user_id",$student->id)->get();
                    if(count($user_cancellations)==0) {
                        $this->info($student->email." - ends_at: ".$subscription->ends_at);
                        try {
                            $start_date=\DateTime::createFromFormat("Y-m-d",$subscription->ends_at);
                            $result=\Chargebee_Subscription::create([
                                'planId' => $student->last_unlimited_subscription,
                                'firstBillingDate' => $start_date,
                            ]);
                            if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                            Subscription::where("id",$subscription->id)->delete();
                        } catch (\Exception $e){
                            \Log::error("Error on resubscribe: ".var_export($e->getMessage(),true)." - User: ".$student->email);
                        }
                    }
                }
            }
        }

        $this->info("\nEnd of the verification!");
    }
}
