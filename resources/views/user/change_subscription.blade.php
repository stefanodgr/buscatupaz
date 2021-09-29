@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    @if(isset($locations))
                        change Location
                    @elseif($user->subscription->status!='cancelled')
                        change Plan
                    @else
                        Re-Subscribe
                    @endif
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">
        <div class="billing-container">
            <div class="billing-title">
                @if(isset($locations))
                    change Location
                @elseif($user->subscription->status=='cancelled')
                    Hey, it looks like you don’t have an active subscription :(
                @else
                    {{ucfirst($user->subscription->plan->location->name)}} Programs
                @endif
            </div>
            <div class="billing-desc billing-light-desc">
                @if(isset($locations))
                    <p>
                        Here you can change your location and choose plans in person in {{ucfirst(implode(', ',$locations))}}!
                    </p>
                    <p>
                        Don’t want to switch location? <a href="{{route('billing')}}">Click here to go back</a>.
                    </p>
                @elseif($user->hasImmersion())
                    <p>
                        Want to take a look at our online programs to do before or after your Immersion experience? Do that here.
                    </p>
                    <p>
                        Not interested? <a href="{{route("billing")}}">Click here to go back</a>.
                    </p>
                @elseif($user->subscription->status!='cancelled')
                    <p>
                        Want to put your subscription on “pause” with Hourly? Do that here.
                    </p>
                    <p>
                        Don’t want to switch plan? <a href="{{route("billing")}}">Click here to go back</a>.
                    </p>
                @else
                    <p>
                        Maybe you cancelled and are coming back (hi!), or maybe your card bounced too many times and your account was cancelled automatically. Either way, you’ll need to give us your credit card info again before you can do anything.
                    </p>
                    <p>
                        Click whichever plan you'd like to subscribe to:
                    </p>
                @endif
                <p>
                    Your card on file{{$user->card_last_four?' ends in '.$user->card_last_four:''}}. This is securely held by our processor ChargeBee, not directly by us - we can't see your card information.
                </p>
            </div>
            <div class="plans-container">
                @foreach($plans as $k=>$plan)
                    <div data-toggle="modal" data-target="#change-subscription-{{$k}}" class="plan-container {{$user->subscription->change && $user->subscription->future->type===$plan->type?'pending':''}} {{!isset($locations) && ($user->subscription->plan_name==$plan->plan_id || ($user->subscription->plan_name == 'baselang_149_trial' && $plan->plan_id == 'baselang_149')) && $user->subscription->status!='cancelled'?'active':''}} {{!isset($locations) && $user->subscription->plan->type==$plan->type && $user->subscription->status=='cancelled'?'last_active':''}}">
                        <div class="switch-options">
                            <div class="switch-title">
                                Confirm change
                            </div>
                            <div class="switch-subscription">
                                <div class="input-container">
                                    <input type="hidden" value="{{$plan->plan_id}}" name="subscription">
                                </div>
                                <div class="switch-text">
                                </div>
                            </div>
                        </div>
                        <div class="plan-title">{{$plan->display_name}}</div>
                        <div class="plan-price">${{$user->subscription->plan_name==$plan->plan_id && $user->subscription->plan->location_name==$plan->location_name && $user->subscription->status?$user->subscription->plan->price:$plan->price}} per month</div>

                        <div class="plan-features">
                            @foreach($plan->features as $featured)
                                <div class="plan-featured">
                                    {{$featured[0]}}
                                    @if(isset($featured[1]))
                                        <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="pending-info">
                            <img src="{{asset("img/clock_pending.png")}}" alt="Level Completed"/> Switch On {{$user->subscription->next_billing->format('F j')}}
                        </div>
						<div class="active-info">
                            <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Active
                        </div>
						<div class="last-active-info">
                            <img src="{{asset("img/check_white.png")}}" alt="Level Completed"/> Last Active Subscription
                        </div>
                    </div>

                    @if(isset($locations) || (($user->subscription->plan_name!=$plan->plan_id && !($user->subscription->plan_name == 'baselang_149_trial' && $plan->plan_id == 'baselang_149')) && $user->subscription->status!='cancelled' && !($user->subscription->change && $plan->type==$user->subscription->future->type)) || $user->subscription->subscription_id == "BaseLang")
                        <div class="modal fade modal-subscription-{{$plan->plan_id}}" role="dialog" id="change-subscription-{{$k}}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Confirm
                                            @if($user->subscription->plan->type=='hourly')
                                                Upgrade
                                            @elseif($plan->type=='hourly')
                                                Downgrade
                                            @else
                                                Switch To {{$plan->display_name}}
                                            @endif
                                        </h5>
                                    </div>
                                    <div class="modal-body">
                                        @if($plan->type=='hourly')
                                            <p>Your plan will downgrade to {{$plan->display_name}} on {{$user->subscription->next_billing->format("F j")}}</p>
                                        @endif
                                        <!-- TODO MESSAGES -->
                                    </div>
                                    <div class="modal-footer">
                                        <!-- TODO DELE TRIAL -->
                                        <a href="{{route('change_subscription_now',[$plan->plan_id])}}" class="btn btn-primary btn-block">
                                            @if($user->subscription->plan->type=='hourly')
                                                Upgrade Now
                                            @elseif($plan->type=='hourly')
                                                Downgrade Now
                                            @else
                                                Switch To {{$plan->display_name}} Now
                                            @endif
                                        </a>
                                            <a href="{{route('change_subscription_end',[$plan->plan_id])}}" class="btn btn-primary btn-block">
                                                @if($plan->type=='hourly')
                                                    Downgrade On {{$user->subscription->next_billing->format("F j")}}
                                                @else
                                                    Switch To {{$plan->display_name}} On {{$user->subscription->next_billing->format("F j")}}
                                                @endif
                                            </a>
                                        <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @if((collect(request()->segments())->last() =='subscription' && $user->subscription->plan->location_name=='online') || (collect(request()->segments())->last() =='location' && $user->subscription->plan->location_name=='medellin'))
                <h4>What's the difference between Real World and DELE? <a href="https://baselang.com/blog/company/realworld-vs-dele/">Read the guide to choose the right program here.</a></h4>
            @endif
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip();

            @if($preview)
                $(".modal-subscription-"+"{{$preview}}").modal('show');
            @endif

        });
    </script>
@endsection