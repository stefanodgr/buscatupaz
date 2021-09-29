@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

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


        <div class="calendar-container">
            <div class="calendar-container-title">
                {{ __('Scheduled Classes') }}
            </div>
            <div class="calendar-container-desc">

                @if($user->isInmersionStudent())
                    <p>Here you’ll find all of your currently booked online classes. If you can’t make it, please cancel beforehand so someone else can take the class. Your in-person GrammarLess program classes aren’t shown here.</p>

                    <p>Your online classes will take place via the Zoom app, which is similar to Skype. You can download the app for free <a target="_blank" href="https://zoom.us/download#client_4meeting">here</a>. Once you’ve created a <a target="_blank" href="https://zoom.us/signup">free account</a>, you should go ahead and add your teacher as a contact on Zoom. <a target="_blank" href="https://baselang.com/support/how-to-use-zoom/">Here is a full guide to using Zoom</a>.</p>

                    <p>You receive an email reminder 10 minutes before each online class starts. If you haven’t done so already, make sure to download Zoom and add your teacher as a contact before the class begins.</p>
                @elseif($user->isSchoolStudent())
                    <p>Here you’ll find all of your currently booked in-person Medellin classes, and online classes. If you can’t make it, please cancel beforehand so someone else can take the class.</p>

                    <p>Your online classes will take place via the Zoom app, which is similar to Skype. You can download the app for free <a target="_blank" href="https://zoom.us/download#client_4meeting">here</a>. Once you’ve created a <a target="_blank" href="https://zoom.us/signup">free account</a>, you should go ahead and add your teacher as a contact on Zoom. <a target="_blank" href="https://baselang.com/support/how-to-use-zoom/">Here is a full guide to using Zoom</a>.</p>

                    <p>You receive an email reminder 10 minutes before each online class starts. If you haven’t done so already, make sure to download Zoom and add your teacher as a contact before the class begins.</p>
                @else
                    <p>{{ __('Here you’ll find all of your currently booked classes') }}. {!! __("Booking_Full_Description") !!}
                @endif

            </div>

            @if(count($classes)>0)
                <div class="classes-confirm">
                    @foreach($classes as $class)
                        <div class="class-confirm">
                            <img src="{{asset("assets/users/photos/".$class->teacher->id.".jpg?v=".rand())}}" alt="{{$class->teacher->first_name}}" />
                            <div class="teacher_name">
                                {{$class->teacher->first_name}} @if($class->location=="online") <span class="teacher_email">Zoom: {{$class->teacher->zoom_email}}</span> @endif
                            </div>
                            <div class="teacher_time">
                                @if($class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d"))
                                    {{ __('Today at') }} {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}@if($class->location=="online"), online @endif
                                @elseif($class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->add(new DateInterval('P1D'))->format("Y-m-d"))
                                    {{ __('Tomorrow at') }} {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}@if($class->location=="online"), online @endif
                                @else
                                    {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("l, d F Y")}} at {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}@if($class->location=="online"), online @endif
                                @endif
                                @if($class->location!="online")
                                    at the {{$class->location}} School
                                @endif
                            </div>
                            <div class="cancel-class" class-id="{{$class->id}}">
                                Cancel
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="nothing-booked">
                    {{ __('You have nothing booked') }}! <a href="{{route("classes_new")}}">{{ __('Click here to book a new class') }}</a>.
                </div>
            @endif
        </div>


        <form id="cancel_class" action="{{route('cancel_classes')}}" method="post">
            <input type="hidden" id="classtocancel" name="class" value="0">
            {{ csrf_field() }}
        </form>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $(".cancel-class").click(function () {
                $("#classtocancel").val($(this).attr("class-id"));
                $("#cancel_class").submit();
                //cancel_class
            });



        })
    </script>
@endsection