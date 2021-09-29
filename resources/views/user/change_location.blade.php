@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    change Location
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="billing">

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!! $error !!}
                </div>
            @endforeach
        @endif

        @if(session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{session('message_info')}}
            </div>
        @endif

        <div class="billing-container">
            <div class="billing-title">
                    change Location
            </div>
            <div class="billing-desc billing-light-desc">
                <p>
                    @if(isset($user) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW" || $user->subscriptionAdquired()->plan->name=="medellin_RW_1199" || $user->subscriptionAdquired()->plan->name=="medellin_RW_Lite" || $user->subscriptionAdquired()->plan->name=="medellin_DELE"))
                        Here you can change to online only classes and stop taking classes in person!
                    @else
                        Here you can change your location and choose plans in person in Medellin!
                    @endif
                </p>
                <p href="{{route("billing")}}">
                    Don’t want to switch location? <a href="{{route("billing")}}">Click here to go back</a>.
                </p>
                <p>
                    @if($user->card_last_four)
                        Your card on file ends in {{$user->card_last_four}}. This is securely held by our processor Chargebee, not directly by us - we can't see your card information.
                    @elseif($user->paypal_email)
                        Your Paypal Account ({{$user->getPaypalEmail()}}) is securely held by our processor Chargebee, not directly by us - we can't see your card information.
                    @endif
                </p>
            </div>
        </div>

        @if(isset($user) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW" || $user->subscriptionAdquired()->plan->name=="medellin_RW_1199" || $user->subscriptionAdquired()->plan->name=="medellin_RW_Lite" || $user->subscriptionAdquired()->plan->name=="medellin_DELE"))
            <div class="plans-containers">
                
                <div class="plans-container">

                    <div class="switch-options">
                        <div class="switch-title">
                            Confirm change
                        </div>
                        <div class="switch-subscription">
                            <div class="input-container">
                                <input type="hidden" value="baselang_hourly" name="subscription">
                            </div>
                            <div class="switch-text">
                                @if($user->subscription && $user->subscription->ends_at)
                                    Confirm change to Hourly Online on {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format("F j, Y")}}, when your current subscription ends, you will be downgraded to the Hourly Online program, and will only be able to take classes online, instead of also in person in Medellin.
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="plan-title">{{$plans["baselang_hourly"][0]}}</div>
                    <div class="plan-price">${{$plans["baselang_hourly"][1]}} per month</div>

                    <div class="plan-features">
                        @foreach($plans["baselang_hourly"][2] as $featured)
                            <div class="plan-featured">
                                {{$featured[0]}}
                                @if(isset($featured[1]))
                                    <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Active
                    </div>

                    <div class="last-active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Last Active Subscription
                    </div>

                </div>
                
                <div class="plans-container">

                    <div class="switch-options">
                        <div class="switch-title">
                            Confirm change
                        </div>
                        <div class="switch-subscription">
                            <div class="input-container">
                                <input type="hidden" value="baselang_129" name="subscription">
                            </div>
                            <div class="switch-text">
                                @if($user->subscription && $user->subscription->ends_at)
                                    Confirm change to Real World Online on {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format("F j, Y")}}, when your current subscription ends, you will be downgraded to the Real World Online program, and will only be able to take classes online, instead of also in person in Medellin.
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="plan-title">{{$plans["baselang_149"][0]}}</div>
                    <div class="plan-price">${{$plans["baselang_149"][1]}} per month</div>

                    <div class="plan-features">
                        @foreach($plans["baselang_149"][2] as $featured)
                            <div class="plan-featured">
                                {{$featured[0]}}
                                @if(isset($featured[1]))
                                    <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Active
                    </div>

                    <div class="last-active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Last Active Subscription
                    </div>

                </div>

                <div class="plans-container">

                    <div class="switch-options">
                        <div class="switch-title">
                            Confirm change
                        </div>
                        <div class="switch-subscription">
                            <div class="input-container">
                                <input type="hidden" value="baselang_dele" name="subscription">
                            </div>
                            <div class="switch-text">
                                @if($user->subscription && $user->subscription->ends_at)
                                    Confirm change to DELE Online on {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format("F j, Y")}}, when your current subscription ends, you will be downgraded to the DELE Online program, and will only be able to take classes online, instead of also in person in Medellin.
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="plan-title">{{$plans["baselang_dele"][0]}}</div>
                    <div class="plan-price">${{$plans["baselang_dele"][1]}} per month</div>

                    <div class="plan-features">
                        @foreach($plans["baselang_dele"][2] as $featured)
                            <div class="plan-featured">
                                {{$featured[0]}}
                                @if(isset($featured[1]))
                                    <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Active
                    </div>

                    <div class="last-active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Last Active Subscription
                    </div>

                </div>
            </div>
        @else
            <div class="plans-containers">

                <div class="plans-container">
                        
                    <div class="switch-options">
                        <div class="switch-title">
                            Confirm change
                        </div>
                        <div class="switch-subscription">
                            <div class="input-container">
                                <input type="hidden" value="medellin_RW_1199" name="subscription">
                            </div>
                            <div class="switch-text">
                                Confirm change to Medellin Real World Starting immediately, you’ll be able to take classes at our in-person school in Medellin, Colombia. You’ll be charged a pro-rated adjustment for the difference in price.
                            </div>
                        </div>
                    </div>

                    <div class="plan-title">{{$plans["medellin_RW_1199"][0]}}</div>
                    <div class="plan-price">${{$plans["medellin_RW_1199"][1]}} per month</div>

                    <div class="plan-features">
                        @foreach($plans["medellin_RW_1199"][2] as $featured)
                            <div class="plan-featured">
                                {{$featured[0]}}
                                @if(isset($featured[1]))
                                    <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Active
                    </div>

                    <div class="last-active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Last Active Subscription
                    </div>

                </div>

                <div class="plans-container">
                        
                    <div class="switch-options">
                        <div class="switch-title">
                            Confirm change
                        </div>
                        <div class="switch-subscription">
                            <div class="input-container">
                                <input type="hidden" value="medellin_RW_Lite" name="subscription">
                            </div>
                            <div class="switch-text">
                                Confirm change to Medellin Real World Lite Starting immediately, you’ll be able to take classes at our in-person school in Medellin, Colombia. You’ll be charged a pro-rated adjustment for the difference in price.
                            </div>
                        </div>
                    </div>

                    <div class="plan-title">{{$plans["medellin_RW_Lite"][0]}}</div>
                    <div class="plan-price">${{$plans["medellin_RW_Lite"][1]}} per month</div>

                    <div class="plan-features">
                        @foreach($plans["medellin_RW_Lite"][2] as $featured)
                            <div class="plan-featured">
                                {{$featured[0]}}
                                @if(isset($featured[1]))
                                    <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Active
                    </div>

                    <div class="last-active-info">
                        <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Last Active Subscription
                    </div>

                </div>

            </div>
        @endif

        <div id="change-subscription" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <p></p>
                        @if(!$is_dele_trial && $user->subscription)
                            <div class="link-diff">
                                Read all the differences between DELE and Real World <a target="_blank" href="https://baselang.com/blog/company/realworld-vs-dele/">here</a>.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <form action="{{route("upgrade_subscription")}}" method="post">
                            {{csrf_field()}}
                            <div class="input-container">
                            </div>

                            <div class="instant instant-option">
                                @if(isset($user) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW" || $user->subscriptionAdquired()->plan->name=="medellin_RW_1199" || $user->subscriptionAdquired()->plan->name=="medellin_RW_Lite" || $user->subscriptionAdquired()->plan->name=="medellin_DELE"))
                                    <button type="submit" class="btn btn-primary btn-block">Confirm</button>
                                @else
                                    <button type="submit" class="btn btn-primary btn-block">Start Now</button>
                                    <button id="change-subscription-date" type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-change-subscription-date">Pick a Date</a>
                                @endif
                            </div>

                            <div class="no-instant instant-option">
                                <button type="button" class="btn btn-primary btn-block change-now">change Now</button>
                                @if($user->subscription)
                                    <button type="button" class="btn btn-primary btn-block change-pending">change on {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format("F j")}}</button>
                                @endif
                            </div>

                            <div class="dele-trial-option">
                                <a id="dele-now" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-dele-trial">Switch to DELE Now</a>
                                <a id="dele-trial-now" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-dele-trial">Take Trial of DELE Now</a>
                                @if($user->subscription)
                                    <a id="dele-trial-date" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-dele-trial">Take Trial of DELE on {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format("F j")}}</a>
                                @endif
                            </div>
                        </form>


                        <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>

                    </div>
                </div>

            </div>
        </div>

        <div id="modal-change-subscription-date" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <form action="{{route("change_location_date")}}" method="post">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Pick a Date</h4>
                        </div>
                        <div class="modal-body">
                            {{ csrf_field() }}
                            <input class="form-control" placeholder="Select the new date" type="date" name="firstBillingDate" id="firstBillingDate" required>
                        </div>
                        <div class="pick-date"></div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary btn-block">Confirm</button>
                            <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip();

            $(".plans-container").click(function(){
                if($(this).hasClass("active")){
                    return false;
                }

                $("#change-subscription").find(".modal-title").text($(this).find(".switch-title").text());
                $("#change-subscription").find("p").text($(this).find(".switch-text").text());

                $("#change-subscription").find(".input-container").html($(this).find(".input-container").html());
                $(".pick-date").html($(this).find(".input-container").html());

                if($(this).find(".dele-trial").length>0){
                    $("#change-subscription").find(".instant-option.instant").hide();
                    $("#change-subscription").find(".instant-option.no-instant").hide();
                    $("#change-subscription").find(".dele-trial-option").show();
                    $("#change-subscription").find(".link-diff").show();
                }
                else{
                    if($(this).find(".instant-change").length>0){
                        $("#change-subscription").find(".instant-option").hide();
                        $("#change-subscription").find(".dele-trial-option").hide();
                        $("#change-subscription").find(".link-diff").hide();
                        $("#change-subscription").find(".instant-option.no-instant").show();
                    } else {
                        $("#change-subscription").find(".instant-option").hide();
                        $("#change-subscription").find(".dele-trial-option").hide();
                        $("#change-subscription").find(".link-diff").hide();
                        $("#change-subscription").find(".instant-option.instant").show();
                    }
                }

                $("#change-subscription").modal("show");
            });

            $(".change-now").click(function () {
                $("#change-subscription .instant-change").val(1);
                $("#change-subscription form").submit();
            });

            $(".change-pending").click(function () {
                $("#change-subscription .instant-change").val(0);
                $("#change-subscription form").submit();
            })

            $("#dele-now").click(function () {
                $('#change-subscription').modal('toggle');

                $("#modal-dele-trial").find(".modal-title").text("Confirm Switch");
                @if($user->subscription)
                    $("#modal-dele-trial").find("p").text("Your currently booked classes with Real World will be cancelled, and your account will be switched to DELE. You will be charged ${{$prorated_amount_one}} for the pro-rated difference in price, and then will be billed $199/mo as normal starting on {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format('F j, Y')}}.");
                @endif
                $("#modal-dele-trial").find("#subscription").val("baselang_dele");
                $("#modal-dele-trial").find("#instant").val(1);
            })

            $("#dele-trial-now").click(function () {
                $('#change-subscription').modal('toggle');

                $("#modal-dele-trial").find(".modal-title").text("Confirm 7-Day Trial");
                $("#modal-dele-trial").find("p").text("If you take the DELE trial now, any booked Real World classes will be cancelled, and you will be switched to a DELE account. You will be charged a pro-rated fee for the difference in price if you decide to stay with DELE. If it's not for you, just click the button on the Billing page to cancel the trial and return to Real World.");
                $("#modal-dele-trial").find("#subscription").val("baselang_dele_test");
                $("#modal-dele-trial").find("#instant").val(1);
            })

            $("#dele-trial-date").click(function () {
                $('#change-subscription').modal('toggle');

                $("#modal-dele-trial").find(".modal-title").text("Confirm 7-Day Trial");
                @if($user->subscription)
                    $("#modal-dele-trial").find("p").text("On {{DateTime::createFromFormat("Y-m-d",date('Y-m-d', strtotime($user->subscription->ends_at. ' + 1 days')))->format('F j, Y')}}, at the end of your billing period for Real World, your account will be switched to DELE. You’ll have a one week trial, and at the end of the trial ({{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->add(new DateInterval('P7D'))->format('F j, Y')}}) you'll be billed $199, and every month thereafter if you do not cancel. If DELE is not for you, just click the button on the Billing page to cancel the trial and return to Real World.");
                @endif
                $("#modal-dele-trial").find("#subscription").val("baselang_dele_test");
                $("#modal-dele-trial").find("#instant").val(0);
                $("#modal-dele-trial").find("#route-form").attr("action","/billing/dele_trial_date");
            })

            $("#change-subscription-date").click(function () {
                $('#change-subscription').modal('toggle');
            });

            $("#firstBillingDate").change(function () {
                var current_day = '{{gmdate("Y-m-d")}}';

                if($(this).val() <= current_day){
                    alert("The date must be greater than the current day!")
                    $(this).val("");
                }

            });

        });
    </script>
@endsection
