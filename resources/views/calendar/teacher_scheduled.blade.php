@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper"></div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="calendar">

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!!$error!!}
                </div>
            @endforeach
        @endif

        @if(session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{session('message_info')}}
            </div>
        @endif

        <div class="calendar-container calendar-container-teacher">
            <div class="calendar-container-title">
                {{ __('Scheduled Classes') }}
            </div>
            <div class="calendar-container-desc">
                <p>{{ __('Here you’ll find all of your currently booked classes') }}.</p>
                <p class="next-class"></p>
            </div>
            <div class="student-main-info student-main-info-before">
                <p>{{ __('No Student Selected') }}</p>
            </div>
            <div class="classes-confirm teacher-classes"></div>
            <div class="student-main-info student-main-info-after">
                <p>{{ __('No Student Selected') }}</p>
            </div>
            <audio id="sndnotification" src="{{asset("audio/classin3minutes.mp3")}}" preload="auto"></audio>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip();

            function loadStudent(student){
                $(".student-main-info").load("/student/"+student,function(){
                    history.pushState({}, "BaseLang", "/teacher/classes/user/"+student);
                });
            }

            @if($student)
                loadStudent({{$student}})
            @endif

            function loadClasses(){
                $(".teacher-classes").load("/teacher/load_classes",function(){});
            }

            setInterval(loadClasses, 60000);

            loadClasses();

        })
    </script>
@endsection