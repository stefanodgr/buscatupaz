@if(count($classes)>0)
    @foreach($classes as $class)
        <div class="class-confirm teacher-class-confirm" student-id="{{$class->student->id}}">
            @if(file_exists("assets/users/photos/".$class->student->id.".jpg"))
                <img src="{{asset("assets/users/photos/".$class->student->id.".jpg?v=".rand())}}" alt="{{$class->student->first_name}}" />
            @else
                <img src="{{ asset('img/user.png') }}" alt="No User Image">
            @endif

            <div class="teacher_name">
                {!! $class->student->isNew()?'<i class="fa fa-rocket" aria-hidden="true"></i>':""!!}
                @if($class->student->isInmersionStudent())
                    <i class="fa fa-comments" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="This is an Immersion school student. Try to focus on conversation only. If the student requests a grammar explanation, you can give it to them, but suggest that they keep these classes to practicing what they already know in general."></i>
                @endif 
                {{$class->student->first_name}}


            </div>
            <div class="teacher_time">
                @if($class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d"))
                    {{ __('Today at') }} {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                @elseif($class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->add(new DateInterval('P1D'))->format("Y-m-d"))
                    {{ __('Tomorrow at') }} {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                @else
                    {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("l, d F Y")}} at {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                @endif
            </div>

                <div class="text-right" style="font-size:12px">

                    <a href="{{route("get_students_progress",["user_id"=>$class->student->id])}}"> <span>{{__('Notes for User')}}</span> </a>

                </div>

            <div class="launch-class" class-id="{{$class->id}}"></div>
        </div>
    @endforeach
@else
    <h3 class="text-center">{{ __('You have nothing booked') }}!</h3>
@endif

<script>

    $('[data-toggle="tooltip"]').tooltip();

    function loadStudent(student){
        $(".student-main-info").load("/student/"+student,function(){
            history.pushState({}, "BaseLang", "/teacher/classes/user/"+student);
        });
    }

    $(".teacher-class-confirm").click(function () {
        $(".teacher-class-confirm").removeClass("active");
        $(this).addClass("active");
        loadStudent($(this).attr("student-id"));
    });

    $(".close-info").click(function(){
        $(".student-main-info").removeClass("active");
    });

    @if($student)
        loadStudent({{$student}})
    @endif

    @foreach($classes as $class)
        @if($class->getClassDateTime()->format("U")-gmdate("U")>0)

            var next_class=parseInt({{($class->getClassDateTime()->format("U")-gmdate("U"))}});

            if(next_class/3600>1){
                $(".calendar-container-desc .next-class").html("{{__('Your Next Class Starts in')}} <span>"+parseInt(next_class/3600)+" {{__('hours')}}</span>");
                //hours
            } else {
                $(".calendar-container-desc .next-class").html("{{__('Your Next Class Starts in')}} <span>"+parseInt(next_class/60)+" {{__('minutes')}}</span>");
                if(parseInt(next_class/60)==3){
                    $('#sndnotification')[0].play();
                }
                //minutes
            }
            @break
        @endif
    @endforeach

    @if(count($classes)==0)
        $(".calendar-container-desc .next-class").html("");
    @endif
</script>