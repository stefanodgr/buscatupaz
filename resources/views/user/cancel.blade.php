@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Cancel
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
                You have not cancelled your account just yet... select an option below:
            </div>
        </div>

        <div class="cancel-info">
            @foreach($reasons as $k=>$reason)
                @if($k=="internet" || $k=="no_longer" || $k=="low_availability" || ($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly" && $k=="take_break"))
                    <form action="{{route("get_confirm_cancel")}}" method="post">
                        <input type="hidden" value="{{csrf_token()}}" name="_token"/>
                        <input type="hidden" value="{{$k}}" name="reason"/>
                        <button type="submit" class="reason-option">
                            {{$reason}}
                        </button>
                    </form>
                @else
                    <a class="reason-option" href="{{route("cancel_subscription_reason",["reason"=>$k])}}">
                        {{$reason}}
                    </a>
                @endif
            @endforeach
        </div>

        <div class="no-cancel-info">
            <p>Don’t want to cancel? <a href="{{route("billing")}}">Click here to go back.</a></p>
        </div>

    </div>

@endsection

@section("scripts")

@endsection