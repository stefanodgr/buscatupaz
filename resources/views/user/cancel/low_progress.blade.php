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
                    Let Us Help You Break Through Your Plateau, Free
                </div>
                <div class="cancel-desc">
                    <div class="cancel-desc">
                        <iframe class="youtube-player" type="text/html" src="https://www.youtube.com/embed/QGiB6sVNzFY?rel=0&showinfo=0&autoplay=1" frameborder="0"></iframe>
                    </div>
                </div>
                <div class="cancel-actions">
                    <a class="btn btn-primary" href="https://calendly.com/baselangcoaching/progresscall">Schedule My Strategy Call</a>
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