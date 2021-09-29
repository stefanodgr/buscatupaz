@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("cancel_subscription")}}">
                    Cancel <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("cancel_subscription_reason",["reason"=>$reason])}}">
                    {{$current_reason}} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Confirm Cancellation
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
            <div class="billing-title billing-cancel-title">
                Confirm Cancellation
            </div>
        </div>

        <div class="cancel-desc">
            @if($user->subscription->plan=="baselang_99")
                <p><b>Once you cancel, you will still have access to BaseLang until {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->format("F j"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->format("F j")}}, since you have already paid for this time.</b></p>
                <p>Between now and then, you can “undo” the cancellation in the billing area if you change your mind.</p>
                <p>We will keep all of your progress and data so that if you decide to return, you can start where you left off.</p>
                <p>However, <b>you will lose the $99/mo “grandfathered” rate that you currently have</b>. If you decide to return, you’ll need to pay $149/mo, or whatever the price is (if we decide to increase it, though we don’t plan on it).</p>
                <p>The only way to keep your $99/mo rate is to keep any active subscription, which you can do by downgrading to Hourly, which is just $9/mo. <a href="{{route("change_subscription")}}">Click here to downgrade to Hourly</a> instead to keep your rate.</p>
            @elseif($user->subscription->plan=="baselang_129" || $user->subscription->plan=="baselang_149")
                <p>
                    <b>Once you cancel, you will still have access to BaseLang until {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->format("F j"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->format("F j")}}, since you have already paid for this time.</b>
                </p>
                <p>
                    Between now and then, you can “undo” the cancellation in the billing area if you change your mind.
                </p>
                <p>
                    We will keep all of your progress and data so that if you decide to return, you can start where you left off.
                </p>
                <p>
                    However, you will lose the @if($user->subscription->plan=="baselang_129") $129/mo @else $149/mo @endif rate if we raise the price before you return. We don’t have plans for this, but it’s something to keep in mind.
                </p>
            @elseif($user->subscription->plan=="baselang_hourly")
                <p>
                    We will keep all of your progress and data so that if you decide to return, you can start where you left off.
                </p>
            @else
                <p>
                    <b>Once you cancel, you will still have access to BaseLang until {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->format("F j"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->format("F j")}}, since you have already paid for this time.</b>
                </p>
                <p>
                    Between now and then, you can “undo” the cancellation in the billing area if you change your mind.
                </p>
                <p>
                    We will keep all of your progress and data so that if you decide to return, you can start where you left off.
                </p>
                <p>
                    However, you will lose the ${{$user->plan->price}}/mo rate if we raise the price before you return. We don’t have plans for this, but it’s something to keep in mind.
                </p>
            @endif

            @if($user->buy_prebooks->first())
                <p>
                    Cancelling will cause you to lose Prebook {{ucwords(strtolower($user->buy_prebooks->first()->type))}}. If you intend on coming back at any point, and don’t want to lose Prebook Silver, we suggest that you <a href="{{route("pause_account")}}">pause your account</a> instead of cancelling.
                </p>
            @endif

        </div>

        <form method="post" action="{{route("subscription_cancel")}}">
            {{ csrf_field() }}
            <div class="cancel-actions">

                <a href="{{route("billing")}}" class="btn btn-primary">Nevermind</a>

                @if($other)
                    <input type="hidden" value="{{$other}}" name="other">
                @endif

                <input type="hidden" value="{{$reason}}" name="reason"/>
                <button type="submit" class="btn btn-outline btn-danger">Confirm Cancellation</button>

            </div>
        </form>

    </div>

@endsection

@section("scripts")

@endsection