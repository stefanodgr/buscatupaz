<?php

namespace Tests\Feature;

use App\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateSubscriptionTest extends TestCase
{
    /**
     * @group user_sub
     */
    public function testCreateUser()
    {
        $response = $this->json('POST', '/external/registeruser', ['serialdata' => ['0' => ['name' => 'fname', 'value' => 'Test'], '1' => ['name' => 'lname', 'value' => 'Imagina'], '2' => ['name' => 'email', 'value' => 'testcase@imaginacolombia.com']]]);
        $response->assertStatus(200);
    }

    /**
     * @group pay_sub
     */
    public function testUpdatePaymentMethod()
    {

        $response = $this->json('POST', '/login', ['email' => 'testcase@imaginacolombia.com', 'password' => '12345']);
        $response->assertStatus(302);

        $response = $this->json('POST', '/profile/zoom_required', ['zoom_email' => 'testcase@imaginacolombia.com']);
        $response->assertStatus(302);

        $response = $this->json('POST', '/billing/updatechargebee', ['payment_method_nonce' => 'fake-valid-nonce']);
        $response->assertStatus(302);

        $response = $this->get('/billing');
        $response->assertStatus(200);
    }

    /**
     * @group update_sub
     */
	public function testCreditsHourly()
    {
        $response = $this->json('POST', '/login', ['email' => 'testcase@imaginacolombia.com', 'password' => '12345']);
        $response->assertStatus(302);

	    //GET Hourly
	    $response = $this->json('POST', '/billing/change', ['subscription' => 'baselang_hourly', 'instant' => 1]);
	    $response->assertStatus(302);

	    //GET RW
	    $response = $this->json('POST', '/billing/change', ['subscription' => 'baselang_129', 'instant' => 1]);
	    $response->assertStatus(302);

	    //GET Hourly
	    $response = $this->json('POST', '/billing/change', ['subscription' => 'baselang_hourly', 'instant' => 1]);
	    $response->assertStatus(302);
    }

    /**
     * @group reset_sub
     */
    public function testReset()
    {
        try {
            $user = User::where("email","testcase@imaginacolombia.com")->first();
            Log::info("Reset Tests User: ".$user->email);

            $user_subscription=$user->getCurrentSubscription();
            if($user_subscription) {
	            Subscription::where("id",$user_subscription->id)->delete();
            	$result = \ChargeBee_Subscription::cancel($user_subscription->subscription_id);
            	if(!$result->success) throw new \Exception(isset($result->message)?$result->message:$result);
            }

            $result = \Chargebee_Customer::delete($user->chargebee_id);
            Log::info("Removing Chargebee ID: ".$user->chargebee_id." status:".var_export($result->success,true));

            User::where("email","testcase@imaginacolombia.com")->delete();
            Log::info("Reset Finished");

            $response = $this->json('POST', '/login', ['email' => 'testcase@imaginacolombia.com', 'password' => '12345']);
            $response->assertStatus(302);
        } catch(\Exception $e){
            Log::error("error on clear test:".var_export($e->getMessage(),true));
        }
    }
}