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

                <a class="breadcrumb-item" href="{{route("cancel_reason",["reason"=>$reason->option])}}">
                    {{$reason->option}} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Confirm Cancellation
                </a>

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">
        <div class="billing-container">
            <div class="billing-title billing-cancel-title">
                Confirm Cancellation
            </div>
        </div>

        <div class="cancel-desc">
            @if($user->subscription->plan->type=="rw" || $user->subscription->plan->type=="dele")
                <p>
                    <b>Once you cancel, you will still have access to BaseLang until {{DateTime::createFromFormat("Y-m-d H:i:s",$user->subscription->ends_at)->format("F j")}}, since you have already paid for this time.</b>
                </p>
                <p>
                    Between now and then, you can “undo” the cancellation in the billing area if you change your mind.
                </p>
                <p>
                    We will keep all of your progress and data so that if you decide to return, you can start where you left off.
                </p>
                @if($user->subscription->plan->name==$default_plan->name)
                    <p>
                        However, <b>you will lose the ${{$user->subscription->plan->price}}/{{$user->subscription->period_unit}} rate if we raise the price before you return</b>. We don’t have plans for this, but it’s something to keep in mind.
                    </p>
                @else
                    <p>
                        However, <b>you will lose the ${{$user->subscription->plan->price}}/{{$user->subscription->period_unit}} “grandfathered” rate that you currently have</b>. If you decide to return, you’ll need to pay {{$default_plan->price}}/month, or whatever the price is (if we decide to increase it, though we don’t plan on it). We don’t have plans for this, but it’s something to keep in mind.
                    </p>
                @endif

                <p>
                    The only way to keep your ${{$user->subscription->plan->price}}/{{$user->subscription->period_unit}} rate is to keep any active subscription, which you can do by downgrading to Hourly, which is just ${{$hourly_plan->price}}/month. <a href="{{route("change_subscription")}}">Click here to downgrade to Hourly</a> instead to keep your rate.
                </p>
            @else
                <p>
                    We will keep all of your progress and data so that if you decide to return, you can start where you left off.
                </p>
            @endif

            @if($user->getPrebook())
                <p>
                    Cancelling will cause you to lose Prebook {{ucwords(strtolower($user->prebook->type))}}. If you intend on coming back at any point, and don’t want to lose Prebook Silver, we suggest that you <a href="{{route("cancel_pause")}}">pause your account</a> instead of cancelling.
                </p>
            @endif

        </div>

        <form method="post" action="{{route("cancel")}}">
            {{ csrf_field() }}
            <div class="cancel-actions">

                <a href="{{route("billing")}}" class="btn btn-primary">Nevermind</a>

                @if(isset($feedback))
                    <input type="hidden" value="{{$feedback}}" name="other">
                @endif

                <input type="hidden" value="{{$reason->id}}" name="reason"/>
                <button type="submit" class="btn btn-outline btn-danger">Confirm Cancellation</button>
            </div>
        </form>

    </div>

@endsection

@section("scripts")

@endsection