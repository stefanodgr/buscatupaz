@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("cancel")}}">
                    Cancel <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Pause Your Account
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">
        <div class="billing-container">
            <div class="billing-title">
                Pause Your Account
            </div>
            <div class="cancel-desc">
                <p>
                    Life gets in the way sometimes, we know. It happens to everyone. That's why we offer the option to pause your account.<br><br>
                    @if($user->subscription->plan->price<$default_subscription->plan->price)
                            Something to note - unlike if you downgrade to Hourly (${{$hourly->price}}/mo, includes an hour of class, helps you not get rusty), pausing or cancelling your account will cause you to <b>lose the special ${{$user->subscription->plan->price}}/{{$user->subscription->period_unit}} rate</b> you currently pay.<br><br>
                            If you pause or cancel your account, and decide to come back at a later date, then you will need to pay the standard monthly price of ${{$default_subscription->plan->price}}/{{$default_subscription->period_unit}} for that subscription.<br><br>
                            If you retain <b>any active subscription</b> (e.g. hourly), then you’ll be locked into the special ${{$user->subscription->plan->price}}/{{$user->subscription->period_unit}} rate when you return.<br><br>
                    @else
                            Something to note - unlike if you downgrade to Hourly (${{$hourly->price}}/mo, includes an hour of class, helps you not get rusty), pausing or cancelling your account will cause you to lose any special monthly rate you currently pay.<br><br>
                            If you pause or cancel your account, and decide to come back at a later date, then you will need to pay the standard monthly price for that subscription.<br><br>
                            If you retain <b>any active subscription</b>, then you’ll be locked into the same rate when you return.<br><br>
                    @endif
                    Either way, we'll still keep all your progress and everything will be normal when you return, though.
                </p>
            </div>
            <div class="div-buttons-pause">
                <a class="btn btn-primary" href="{{route('change_subscription_preview',['subscription'=>'baselang_hourly'])}}">Downgrade to Hourly</a>
                @if(gmdate("Y-m-d") > DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new DateInterval("P7D"))->format("Y-m-d"))
                    <a class="btn btn-primary" href="{{route("pause_account")}}">Pause Account</a>
                @endif
                <a class="btn btn-outline btn-danger" href="{{route("cancel_survey")}}">I Just Want To Cancel</a>
            </div>

        </div>

    </div>

@endsection

@section("scripts")

@endsection