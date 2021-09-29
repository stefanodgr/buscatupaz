@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Fully Cancel
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
            <p>Once it is completely canceled, your BaseLang account will not be reactivated on {{DateTime::createFromFormat("Y-m-d",$user->pause_account->activation_day)->format("F j")}}.</p>

            <p>It should be noted that we will keep all your progress and data so that, if you decide to return, you can start where you left off.</p>

            <p>One than to note - unlike if you downgrade to Hourly ($9/mo, includes an hour of class, helps you not get rusty), pausing your account will cause you to lose any special rates you may have. If the price goes up (which we aren't planning on at the moment, but can't guarantee will never happen), you won’t be "grandfathered" at the old rate.</p>
        </div>
        <div class="div-buttons-pause">
            <a class="btn btn-primary" data-toggle="modal" data-target="#change-subscription">Downgrade to Hourly</a>
            <a class="btn btn-outline btn-danger" href="{{route("stop_pause_subscription")}}">Confirm Cancellation</a>
            <a class="btn btn-default" href="{{route("billing")}}">Nevermind</a>
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