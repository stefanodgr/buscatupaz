<?php

namespace Tests\Feature;

use App\Models\BuyPrebook;
use App\Models\Subscription;
use App\User;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PrebookTest extends TestCase
{
    /**
     * @group create_user
     */
    public function testCreateUser()
    {
        $response = $this->json('POST', '/external/registeruser', ['serialdata' => ['0' => ['name' => 'fname', 'value' => 'Test'], '1' => ['name' => 'lname', 'value' => 'Imagina'], '2' => ['name' => 'email', 'value' => 'testcase@imaginacolombia.com']]]);
        $response->assertStatus(200);
    }


    public function testUpdatePaymentMethod(){

        $response = $this->json('POST', '/login', ['email' => 'testcase@imaginacolombia.com', 'password' => '12345']);
        $response->assertStatus(302);

        $response = $this->json('POST', '/profile/zoom_required', ['zoom_email' => 'testcase@imaginacolombia.com']);
        $response->assertStatus(302);

        $response = $this->json('POST', '/billing/updatechargebee', ['payment_method_nonce' => 'fake-valid-nonce']);
        $response->assertStatus(302);

        $response = $this->get('/billing');
        $response->assertStatus(200);
    }


    public function testPlan129()
    {
        $response = $this->json('POST', '/login', ['email' => 'testcase@imaginacolombia.com', 'password' => '12345']);
        $response->assertStatus(302);

        $plans=["baselang_129","baselang_129_trial","baselang_99","baselang_99_trial","baselang_dele","baselang_dele_trial","9zhg","baselang_dele_realworld","baselang_hourly"];

        foreach($plans as $plan){
            $teacher_id=($plan=="baselang_dele" || $plan=="baselang_dele_trial" || $plan="baselang_dele_realworld")?3430:4318;

            Log::info("Started Test For Plan: ".$plan);
            //GET A PLAN
            $response = $this->json('POST', '/billing/change', ['subscription' => $plan, 'instant' => 1]);
            $response->assertStatus(302);

            //GO prebook new with teacher
            $response = $this->get('/prebook/prebook_availability');
            $response->assertStatus(200);

            $response = $this->get('/prebook/prebook_availability/'.$teacher_id);
            $response->assertStatus(200);

            //GO PREBOOK
            $response = $this->get('/billing/prebook');
            $response->assertStatus(200);

            //BUY PREBOOK - SILVER
            $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'silver']);
            $response->assertStatus(302);

            //GO BILLING
            $response = $this->get('/billing');
            $response->assertStatus(200);

            //GO PREBOOK IF GET PREBOOK
            $response = $this->get('/billing/prebook');
            $response->assertStatus(302);

            //GO ELECTIVE SILVER
            $response = $this->get('/electives/business/International-Trade-4');
            $response->assertStatus(200);

            //BUY PREBOOK IF GET PREBOOK
            $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'silver']);
            $response->assertStatus(302);

            $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'gold']);
            $response->assertStatus(302);

            //UPGRADE PREBOOK GOLD
            $response = $this->json('POST', '/billing/upgrade_prebook_gold');
            $response->assertStatus(302);

            //UPGRADE AGAIN
            $response = $this->json('POST', '/billing/upgrade_prebook_gold');
            $response->assertStatus(302);

            //GO ELECTIVE GOLD
            $response = $this->get('/electives/business/International-Trade-4');
            $response->assertStatus(200);

            //GO prebook
            $response = $this->get('/prebook');
            $response->assertStatus(200);

            //GO prebook new
            $response = $this->get('/prebook/new');
            $response->assertStatus(200);

            //GO prebook new with teacher
            $response = $this->get('/prebook/new/'.$teacher_id);
            $response->assertStatus(200);

            //GO prebook new with teacher
            $response = $this->get('/prebook/calendar');
            $response->assertStatus(200);

            $response = $this->get('/prebook/calendar/'.$teacher_id);
            $response->assertStatus(200);

            //GO prebook new with teacher
            $response = $this->get('/prebook/success');
            $response->assertStatus(200);

            //GO prebook new with teacher
            $response = $this->get('/prebook/prebook_availability');
            $response->assertStatus(302);

            $response = $this->get('/prebook/prebook_availability/'.$teacher_id);
            $response->assertStatus(302);

            //CANCEL PREBOOK
            try {
                $user = User::where("email","testcase@imaginacolombia.com")->first();
                Log::info("Delete Prebook ".$user->email);

                $buy_prebook = BuyPrebook::where("user_id",$user->id)->delete();
                Log::info("Prebook Deleted ".var_export($buy_prebook,true));
            } catch(\Exception $e){
                Log::error("error on clear prebook: ".var_export($e->getMessage(),true));
            }

            //BUY PREBOOK GOLD
            $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'gold']);
            $response->assertStatus(302);


            //CANCEL PREBOOK
            try {
                $user = User::where("email","testcase@imaginacolombia.com")->first();
                Log::info("Delete Prebook".$user->email);

                $buy_prebook = BuyPrebook::where("user_id",$user->id)->delete();
                Log::info("Prebook Deleted".var_export($buy_prebook,true));
            } catch(\Exception $e){
                Log::error("error on clear prebook:".var_export($e->getMessage(),true));
            }

            //TRY WITHOUT PAYMENT METHOD
            try {
                $user = User::where("email", "testcase@imaginacolombia.com")->first();
                $payment_method_token=$user->payment_method_token;

                $user->payment_method_token="";
                User::where("id", $user->id)->update(["payment_method_token"=>""]);
                Log::info("Payment Method Clean");

                $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'gold']);
                $response->assertStatus(302);

                BuyPrebook::where("user_id",$user->id)->delete();
                Log::info("Prebook Deleted".var_export($buy_prebook,true));

                $user->payment_method_token=$payment_method_token;
                User::where("id", $user->id)->update(["payment_method_token"=>$payment_method_token]);
                Log::info("Payment Setup");

            } catch(\Exception $e){
                Log::error("Error on Clean Chargebee Payment Method:".var_export($e->getMessage(),true));
            }


            //TRY WITHOUT Chargebee ID
            try {
                $user = User::where("email", "testcase@imaginacolombia.com")->first();
                $chargebee_id=$user->chargebee_id;
                $user->chargebee_id="";
                User::where("id", $user->id)->update(["chargebee_id"=>""]);
                Log::info("Chargebee ID Clean");

                $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'gold']);
                $response->assertStatus(302);

                BuyPrebook::where("user_id",$user->id)->delete();
                Log::info("Prebook Deleted".var_export($buy_prebook,true));

                $user->chargebee_id=$chargebee_id;
                User::where("id", $user->id)->update(["chargebee_id"=>$chargebee_id]);
                Log::info("Chargebee ID Setup");

            } catch(\Exception $e){
                Log::error("Error on Clean Chargebee ID:".var_export($e->getMessage(),true));
            }

            //TRY WITHOUT PAYMENT METHOD AND CHARGEBEE ID
            try {
                $user = User::where("email", "testcase@imaginacolombia.com")->first();
                $chargebee_id=$user->chargebee_id;
                $payment_method_token=$user->payment_method_token;

                $user->chargebee_id="";
                $user->payment_method_token="";

                User::where("id", $user->id)->update(["chargebee_id"=>"","payment_method_token"=>""]);
                Log::info("Chargebee DATA Clean");

                $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'gold']);
                $response->assertStatus(302);

                BuyPrebook::where("user_id",$user->id)->delete();
                Log::info("Prebook Deleted".var_export($buy_prebook,true));

                $user->chargebee_id=$chargebee_id;
                $user->payment_method_token=$payment_method_token;
                User::where("id", $user->id)->update(["chargebee_id"=>$chargebee_id,"payment_method_token"=>$payment_method_token]);
                Log::info("Chargebee DATA Setup");

            } catch(\Exception $e){
                Log::error("Error on Clean Chargebee DATA:".var_export($e->getMessage(),true));
            }

            //REMOVE SUBSCRIPTION
            if($plan!="baselang_hourly")
            {
                try
                {
                    $user_subscription=$user->getCurrentSubscription();
                    Subscription::where("id",$user_subscription->id)->delete();
                    $result = \Chargebee_Subscription::cancel($user_subscription->subscription_id);
                    if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
                }
                catch (\Exception $e)
                {
                    Log::error('Error Deleting Subscription ID: '.var_export($e->getMessage(),true));
                }
            } else {
                //ADD CREDITS TO CHECK BOOK
                User::where("email","testcase@imaginacolombia.com")->update(["credits"=>100]);
            }

            Log::info("Finished Test For Plan: ".$plan);
        }

        //CREATE SILVER
        $response = $this->json('POST', '/billing/buy_prebook', ['type' => 'silver']);
        $response->assertStatus(302);

        //CHECK PREBOOK LIMITS
        $response = $this->json('POST', '/billing/buy_prebook', ["selecteds"=>["5,07:00PM,".$teacher_id,"5,08:00PM,".$teacher_id,"6,07:00PM,".$teacher_id,"6,08:00PM,".$teacher_id,"7,07:00PM,".$teacher_id,"1,12:00PM,3482","2,12:00PM,3482","3,12:00PM,3482","1,12:30PM,3482","2,12:30PM,3482","3,12:30PM,3482"]]);
        $response->assertStatus(302);

        //DO PREBOOK
        $response = $this->json('POST', '/billing/buy_prebook', ["selecteds"=>["5,07:00PM,".$teacher_id,"5,08:00PM,".$teacher_id,"6,07:00PM,".$teacher_id,"6,08:00PM,".$teacher_id,"7,07:00PM,".$teacher_id]]);
        $response->assertStatus(302);

        //CRON CHECK cron/active-prebook
            //CHECK SINGLE
            $response = $this->get('/cron/active-prebook');
            $response->assertStatus(200);

            //CHECK IF REMOVE PREBOOKS
            try {
                BuyPrebook::where("user_id",$user->id)->update(["activation_date"=>"2018-10-01"]);
            } catch (\Exception $e){
                Log::error('Error change prebook: '.var_export($e->getMessage(),true));
            }
            $response = $this->get('/cron/active-prebook');
            $response->assertStatus(200);
    }

    public function testReset()
    {
        try {
            $user = User::where("email","testcase@imaginacolombia.com")->first();
            Log::info("Reset Tests User: ".$user->email);
            $result = \ChargeBee_Customer::delete($user->chargebee_id);
            Log::info("Removing Chargebee ID".$user->chargebee_id." status:".var_export($result->success,true));
            User::where("email","testcase@imaginacolombia.com")->delete();
            Log::info("Reset Finished");
            $response = $this->json('POST', '/login', ['email' => 'testcase@imaginacolombia.com', 'password' => '12345']);
            $response->assertStatus(302);
        } catch(\Exception $e){
            Log::error("error on clear test:".var_export($e->getMessage(),true));
        }
    }
}
