@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("prebook_new")}}">
                    Prebook More Classes <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Prebooked!
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
                Prebooked!
            </div>
            <div class="calendar-container-desc">
                <p>
                    {{ __('These classes will now show up in your') }} <a href="{{route("prebook")}}">Prebook</a>.
                </p>
                <p>
                	If you want to stop any of these classes from continuing to be prebooked, cancel them from the <a href="{{route("prebook")}}">Prebook</a> section. 
                </p>
                <p>If on any given week you can’t make one of the classes, you can cancel just that week’s class in the {{ __('Scheduled Classes') }} section. Each prebooked class will show up in <a href="{{route("classes")}}">{{ __('Scheduled Classes') }}</a> two weeks beforehand.</p>
            </div>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
        });
    </script>
@endsection