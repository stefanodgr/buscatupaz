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
                    Survey
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">

        <div class="billing-container">
            <div class="billing-title billing-cancel-title">
                You have not cancelled your account just yet... select an option below:
            </div>
        </div>

        <div class="cancel-info">
            @foreach($reasons as $k=>$reason)
                <a href="{{route('cancel_reason',['reason'=>$reason->option])}}">{{$reason->option}}</a>
            @endforeach
        </div>

        <div class="no-cancel-info">
            <p>Don’t want to cancel? <a href="{{route("billing")}}">Click here to go back.</a></p>
        </div>

    </div>

@endsection

@section("scripts")

@endsection