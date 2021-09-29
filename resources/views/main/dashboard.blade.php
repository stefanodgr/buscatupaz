@extends("layouts.main")

@section("content")
    <div class="main-content-wrapper" id="dashboard">

        {{--
        @if(session("current_subscription")!="dele")
            <div class="level-progress">
                <div class="progress-circle">
                    <div class="c100 p{{$level_progress->percentage}} imagina">
                        <span>{{$level_progress->level}}</span>
                        <div class="slice">
                            <div class="bar"></div>
                            <div class="fill"></div>
                        </div>
                    </div>
                </div>

                @if($level_progress->level!=10)
                    <div class="content-progress">
                        <div class="content-progress-title">
                            Level {{$user->user_level}} Progress
                        </div>

                        <div class="content-progress-type">
                            <div class="content-progress-type-title">
                                {{$level_progress->core}}/{{$level_progress->core_required}} Core Lessons completed
                            </div>
                            @if($level_progress->next_lesson)
                                <div class="content-progress-type-next">
                                    Next Up: <a href="{{route("lesson",["type"=>$level_progress->next_lesson->level->type,"level_slug"=>$level_progress->next_lesson->level->slug,"lesson_slug"=>$level_progress->next_lesson->slug])}}">{{$level_progress->next_lesson->name}}</a>
                                </div>
                            @endif

                            @if($user->user_level>=3)
                                <div class="content-progress-type-title">
                                    {{$level_progress->electives}}/{{$user->requiredElectives()}} Electives completed
                                </div>
                                @if($level_progress->electives<15)
                                    <div class="content-progress-type-next">
                                        Take any <a href="{{route("electives")}}">elective</a> to fulfill this requirement
                                    </div>
                                @endif
                            @endif

                            @if($level_progress->core_required==0)
                                <p class="level-instructions">
                                    You’ve completed all the requirements for this level. Book a one-hour long class and request the level {{$user->user_level}} test to progress to the next level.
                                </p>
                            @elseif(($level_progress->core_required!=0 && $level_progress->core/$level_progress->core_required==1) && ($user->user_level<=3 || $level_progress->electives>=15))
                                <p class="level-instructions">
                                    You’ve completed all the requirements for this level. Book a one-hour long class and request the level {{$user->user_level}} test to progress to the next level.
                                </p>
                            @endif

                        </div>
                    </div>
                    @if(($level_progress->core_required!=0 && $level_progress->core/$level_progress->core_required==1) && ($user->user_level<=3 || $level_progress->electives>=15))
                    <p class="level-instructions-responsive">
                        You’ve completed all the requirements for this level. Book a one-hour long class and request the level {{$user->user_level}} test to progress to the next level.
                    </p>
                    @endif
                @else
                    <div class="content-progress">
                        <div class="content-progress-title">
                            Course finished!
                        </div>

                        <div class="content-progress-type">
                            <div class="content-progress-type-title">
                                {{$user->first_name}}, congratulations for reaching level 10 in BaseLang !. We invite you to continue practicing to continue reinforcing your knowledge. Keep going!
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @endif
--}}
        @if($classes->count())
            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    {{ __('Scheduled Classes') }}
                </div>
                <div class="content-dashboard-desc">
                    <p>These are your upcoming classes. Please cancel if you can’t make it.</p>
                </div>

                <div class="content-dashboard-classes">

                    @foreach($classes as $class)
                        <div class="class-confirm">
                            <img src="{{asset("assets/users/photos/".$class->teacher->id.".jpg?v=".rand())}}" alt="{{$class->teacher->first_name}}" />
                            <div class="teacher_name">
                                {{$class->teacher->first_name}} <span class="teacher_email">Zoom: ({{$class->teacher->zoom_email}})</span>
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
            </div>
        @endif

        <div class="book-class content-dashboard">
            <div class="content-dashboard-title">
                {{ __('Book New Class') }}
            </div>
            <div class="content-dashboard-desc">
                @if(session("current_subscription")=="dele")
                    <p>Keep learning! Consistently taking classes is the best way to continue to progress.</p>
                @else
                    <p>
                        @if($user->user_level<=3)
                            {{ __('Keep learning! At your level, we suggest just taking each consecutive class, and getting your pronunciation down') }}.
                        @elseif($user->user_level==4)
                            Keep learning! At your level, we suggest a mix of mostly new classes and some pure conversation time.
                        @elseif($user->user_level<=6)
                            Keep learning! At your level, we suggest a roughly 50/50 mix of new material and pure conversation practice.
                        @else
                            Keep learning! At your level, we suggest mostly focusing on conversation time where you really push yourself and work on your weaknesses, with a bit of time for remaining lessons.
                        @endif
                    </p>
                @endif

            </div>

            <div class="content-dashboard-actions">
                @if($user->location_id && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW" || $user->subscriptionAdquired()->plan->name=="medellin_RW_1199" || $user->subscriptionAdquired()->plan->name=="medellin_RW_Lite" || $user->subscriptionAdquired()->plan->name=="medellin_DELE"))
                    @if($user->getFavoriteTeacher())
                        <a href="{{route("classes_new_teacher",["teacher_id"=>$user->getFavoriteTeacher()->id])}}" class="btn btn-primary btn-block">Book Online Class with {{$user->getFavoriteTeacher()->first_name}}</a>
                        @if($user->getFavoriteTeacher()->location_id)
                            <a href="{{route("classes_user_new_teacher",["teacher_id"=>$user->getFavoriteTeacher()->id])}}" class="btn btn-primary btn-block">Book In-Person Class with {{$user->getFavoriteTeacher()->first_name}}</a>
                        @endif
                    @endif
                    <a href="{{route("classes_new")}}" class="btn btn-primary btn-block">Book Online Class by Time</a>
                    <a href="{{route("classes_in_person_new")}}" class="btn btn-primary btn-block">Book In-Person Class by Time</a>
                    <a href="{{route("classes_new_teacher",["teacher_id"=>$first_teacher])}}" class="btn btn-primary btn-block">Book Online Class by Teacher</a>
                    <a href="{{route("classes_user_new_teacher",["teacher_id"=>$first_teacher])}}" class="btn btn-primary btn-block">Book In-Person Class by Teacher</a>
                @else
                    @if($user->getFavoriteTeacher())
                        <a href="{{route("classes_new_teacher",["teacher_id"=>$user->getFavoriteTeacher()->id])}}" class="btn btn-primary btn-block">{{ __('Book New Class') }} with {{$user->getFavoriteTeacher()->first_name}}</a>
                    @endif
                    <a href="{{route("classes_new")}}" class="btn btn-primary btn-block">{{ __('Book New Class by Time') }}</a>
                    <a href="{{route("classes_new_teacher",["teacher_id"=>$first_teacher])}}" class="btn btn-primary btn-block">{{ __('Book New Class by Teacher') }}</a>
                @endif
            </div>
        </div>

        @if(!$user->location_id && isset($days) && $days>=7)
            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    Get BaseLang Free
                </div>
                <div class="content-dashboard-desc">
                    <p>Want to learn for free? We have several ways you can get free time with us. <a href="{{route("referral_page")}}">Learn more here.</a></p>
                </div>
            </div>
        @endif

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
