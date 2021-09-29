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
                    {{$current_reason}}
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

        <div class="billing-container billing-container-reason">
            <form action="{{route("get_confirm_cancel")}}" method="post">
                {{ csrf_field() }}
                <div class="billing-title">
                    Taking A Break? Don’t Cancel, Do This Instead:
                </div>
                <div class="cancel-desc">

                    <iframe class="youtube-player" type="text/html" src="https://www.youtube.com/embed/KEmeHqOOYKc?rel=0&showinfo=0&autoplay=1" frameborder="0"></iframe>

                    <p>
                        Since you’re just taking a break, and will come back, it’s probably because you don’t have enough time to get your money’s worth out of BaseLang. Your life is too busy.
                    </p>
                    <p>
                        But you also don’t want to lose everything you’ve learned so far - you need to keep practicing!
                    </p>
                    <p>
                        By downgrading to Hourly on this page, you’ll get two hours of class (30 minutes of review every week to keep fresh) instead of just one included in your Hourly subscription. And it’s just $9/mo.
                    </p>
                    <p>
                        Don’t get rusty and have to re-learn things when you come back. Downgrade to Hourly and do 30 minutes of review a week to stay fresh - everyone has time for that!
                    </p>
                </div>
                <div class="cancel-actions">
                    <a href="{{route("change_subscription_hourly")}}" class="btn btn-primary">Switch to Hourly With Extra Hour</a>
                    <input type="hidden" value="{{$reason}}" name="reason"/>
                    <button type="submit" class="btn btn-outline btn-danger">I Still Want to Cancel</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {


        })
    </script>
@endsection