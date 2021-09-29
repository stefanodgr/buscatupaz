@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Pause Account
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="calendar">

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

        <div class="calendar-container booked-container">
            <div class="calendar-container-title">
                <img src="{{asset("img/booked.png")}}" title="booked icon"/>
                Successful pause!
            </div>
            <div class="calendar-container-desc">
                <p>
                    Got it! We've paused your account for {{$time}}.
                </p>
                <p>
                    The pause will begin from {{$user->subscription->status=="future"?DateTime::createFromFormat("Y-m-d",$user->subscription->starts_at)->format("F j, Y"):DateTime::createFromFormat("Y-m-d",$user->subscription->ends_at)->format("F j, Y")}} and your subscription will automatically be renewed on {{DateTime::createFromFormat("Y-m-d",$user->activation_day)->format("F j, Y")}}.
                </p>
            </div>
        </div>
    </div>

@endsection

@section("scripts")

@endsection