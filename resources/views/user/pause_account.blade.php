@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Pause Your Account
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="billing">

        @if($errors->any())
            @foreach ($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!! $error !!}
                </div>
            @endforeach
        @endif

        @if (session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{ session('message_info') }}
            </div>
        @endif

        <div class="billing-container">
            <div class="billing-title">
                Pause Your Account
            </div>
            <div class="cancel-desc">
                @if($user_subscription->plan=="baselang_99")
                    <p>
                        Life gets in the way sometimes, we know. It happens to everyone. That's why we offer the option to pause your account.<br><br>

                        Something to note - unlike if you downgrade to Hourly ($9/mo, includes an hour of class, helps you not get rusty), pausing or cancelling your account will cause you to <b>lose the special $99/mo rate</b> you currently pay.<br><br>

                        If you pause or cancel your account, and decide to come back at a later date, then you will need to pay the standard monthly price of $149/mo for that subscription.<br><br>

                        If you retain <b>any active subscription</b> (e.g. hourly), then you’ll be locked into the special $99 rate when you return.<br><br>

                        Either way, we'll still keep all your progress and everything will be normal when you return, though.
                    </p>
                @elseif($user_subscription->plan=="baselang_129")
                    <p>
                        Life gets in the way sometimes, we know. It happens to everyone. That's why we offer the option to pause your account.<br><br>

                        Something to note - unlike if you downgrade to Hourly ($9/mo, includes an hour of class, helps you not get rusty), pausing or cancelling your account will cause you to <b>lose the special $129/mo rate</b> you currently pay.<br><br>

                        If you pause or cancel your account, and decide to come back at a later date, then you will need to pay the standard monthly price of $149/mo for that subscription.<br><br>

                        If you retain <b>any active subscription</b> (e.g. hourly), then you’ll be locked into the special $129 rate when you return.<br><br>

                        Either way, we'll still keep all your progress and everything will be normal when you return, though.
                    </p>
                @else
                    <p>
                        Life gets in the way sometimes, we know. It happens to everyone. That's why we offer the option to pause your account.<br><br>

                        Something to note - unlike if you downgrade to Hourly ($9/mo, includes an hour of class, helps you not get rusty), pausing or cancelling your account will cause you to lose any special monthly rate you currently pay.<br><br>

                        If you pause or cancel your account, and decide to come back at a later date, then you will need to pay the standard monthly price for that subscription.<br><br>

                        If you retain <b>any active subscription</b>, then you’ll be locked into the same rate when you return.<br><br>

                        Either way, we'll still keep all your progress and everything will be normal when you return, though.
                    </p>
                @endif
            </div>
            <div class="div-buttons-pause">
                <a class="btn btn-primary" data-toggle="modal" data-target="#change-subscription">Downgrade to Hourly</a>
                @if(gmdate("Y-m-d") > DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new DateInterval("P7D"))->format("Y-m-d"))
                    <a class="btn btn-primary" href="{{route("pause_account")}}">Pause Account</a>
                @endif
                <a class="btn btn-outline btn-danger" href="{{route("cancel_subscription_survey_page")}}">I Just Want To Cancel</a>
            </div>

        </div>

    </div>

    <div id="change-subscription" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm Downgrade</h4>
                </div>
                <div class="modal-footer">
                    <form action="{{route("upgrade_subscription")}}" method="post">
                        {{csrf_field()}}
                        <div class="input-container">
                            <input type="hidden" value="baselang_hourly" name="subscription">
                        </div>

                        <div class="instant instant-option">
                            <button type="submit" class="btn btn-primary btn-block">Confirm</button>
                        </div>
                    </form>

                    <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>

                </div>
            </div>

        </div>
    </div>

@endsection

@section("scripts")

@endsection