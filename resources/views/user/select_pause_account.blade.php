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

                <a class="breadcrumb-item">
                    I'm going to take a break, I'll be back! <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Pause Account
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
                Pause Account
            </div>
            <div class="cancel-desc">
                <p>
                    Pick how long you'd like to pause for. We’ll email you 3 days before the restart date to ensure you're still ready (you'll be able to extend again from that email, if you need to), no surprise charges. <br><br>

                    The pause will start once the time you have already paid for runs out-you'll still
                    have access until then. For you, that's {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->format("F j, Y")}}.
                </p>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <form action="{{route("post_pause_account")}}" method="post">
                        {{csrf_field()}}
                        <select class="form-control" name="activation_day">
                            <option value="{{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P14D"))->format("Y-m-d"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P14D"))->format("Y-m-d")}}">2 weeks (Restart on {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P14D"))->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P14D"))->format("F j, Y")}})</option>
                            <option value="{{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P28D"))->format("Y-m-d"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P28D"))->format("Y-m-d")}}">1 month (Restart on {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P28D"))->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P28D"))->format("F j, Y")}})</option>
                            <option value="{{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P42D"))->format("Y-m-d"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P42D"))->format("Y-m-d")}}">6 weeks (Restart on {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P42D"))->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P42D"))->format("F j, Y")}})</option>
                            <option value="{{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P58D"))->format("Y-m-d"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P58D"))->format("Y-m-d")}}">2 months (Restart on {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P58D"))->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P58D"))->format("F j, Y")}})</option>
                            <option value="{{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P90D"))->format("Y-m-d"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P90D"))->format("Y-m-d")}}">3 months (Restart on {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->add(new DateInterval("P90D"))->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->add(new DateInterval("P90D"))->format("F j, Y")}})</option>
                        </select>
                        <br>
                        <button class="btn btn-primary">Pause Account</button>
                        <a class="btn btn-default"href="{{route("billing")}}">Nevermind</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section("scripts")

@endsection