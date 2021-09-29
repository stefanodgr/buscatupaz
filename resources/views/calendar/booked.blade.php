@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("classes_new")}}">
                    {{ __('Select Times') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("classes_new")}}">
                    {{ __('Choose Teachers') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("classes_new")}}">
                    {{ __('Confirm Classes') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    {{ __('Booked') }}!
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
                {{ __('Booked') }}!
            </div>
            <div class="calendar-container-desc">
                    <p>
                        {{ __('These classes will now show up in your') }} <a href="{{route("classes")}}">{{ __('Scheduled Classes') }}</a>.
                    </p>
                @if(!$user->getGoogleToken())
                    <p>
                        {{ __('To download the .ics calendar file for these classes') }}, <a href="{{route("ics_classes")}}">{{__("click here")}}.</a> {{ __('If you use') }} {{ __('Google Calendar') }}, {{ __('you can connect that so these get automatically added in the future') }} <a href="{{route("profile")}}">{{__('here')}}</a>.
                    </p>
                    <p>
                        {{ __('You’ll also get a confirmation email soon. Enjoy your class') }}!
                    </p>
                @else
                    <p>This class has been automatically added to your Google Calendar.</p>
                    <p>{{ __('You’ll also get a confirmation email soon. Enjoy your class') }}!</p>
                @endif

                    @if ($unknown_teachers)
                        <p>
                            @if($unknown_teachers->single)
                                Since this is your first class with {{$unknown_teachers->teacher}}and you booked fairly last minute, please add them yourself so the class can start on time. <a href="https://baselang.com/support/add-teacher-zoom-contact/">Here's a step-by-step guide</a>. The teacher's zoom username is {{$unknown_teachers->teacher_email}}.
                            @else
                                Since this is your first class with {{$unknown_teachers->teacher}}and you booked fairly last minute, please add them yourself so the class can start on time. <a href="https://baselang.com/support/add-teacher-zoom-contact/">Here's a step-by-step guide</a>. The zoom usernames are {{$unknown_teachers->teacher_email}}.
                            @endif

                        </p>
                    @endif


            </div>



        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
        })
    </script>
@endsection