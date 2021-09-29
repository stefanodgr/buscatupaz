@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="progress">

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

        <div class="progress-container progress-container-full">
            <div class="progress-container-title">
                    <div class="c100 p100 imagina">
                    <span class="image">
                        @if(file_exists("assets/users/photos/".$student->id.".jpg"))
                            <img src="{{ asset('assets/users/photos/'.$student->id.'.jpg?v='.rand()) }}" alt="No User Image">
                        @else
                            <img src="{{ asset('img/user.png') }}" alt="No User Image">
                        @endif
                    </span>
                    <div class="slice">
                        <div class="bar"></div>
                        <div class="fill"></div>
                    </div>
                </div>

                <h1><span>{{$student->first_name}} {{$student->last_name}}</span></h1>
                {{-- <h1>The BaseLang level of {{$student->first_name}} is <span>{{$student->user_level}}</span></h1> --}}
            </div>
            <br>
            <div class="progress-container-desc">
                <a href="{{route("dashboard")}}" class="btn btn-primary">{{ __('Back') }}</a>
            </div>
        </div>
            {{--
                    @if($subscriptionType!="dele")

                    <div class="progress-container progress-container-half">
                        <div class="container-fluid">
                            <div class="row">

                                                    <div class="col-xs-12 col-sm-7 col-progress-sum">

                                                        <div class="progress-container-title">
                                                            <div class="c100 p{{$level_progress->percentage}} imagina">
                                                                <span>
                                                                    {{$level_progress->level}}
                                                                </span>
                                                                <div class="slice">
                                                                    <div class="bar"></div>
                                                                    <div class="fill"></div>
                                                                </div>
                                                            </div>


                                                            <h1>Level {{$student->user_level}} Progress</h1>

                                                            @if($level_progress->core_required!=0)
                                                                <div class="content-progress-type">
                                                                        <div class="content-progress-type-title">
                                                                            {{$level_progress->core}}/{{$level_progress->core_required}} Core Lessons completed
                                                                        </div>
                                                                    @if($level_progress->next_lesson)
                                                                        <div class="content-progress-type-next">
                                                                            Next Up: {{$level_progress->next_lesson->name}}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                            @if($student->user_level>3)
                                                                <div class="content-progress-type">
                                                                    <div class="content-progress-type-title">
                                                                        {{$level_progress->electives}}/{{$student->requiredElectives()}} Electives completed
                                                                    </div>
                                                                </div>
                                                            @endif

                                                        </div>

                                </div>

                                <div class="col-xs-12 col-sm-5 col-progress-desc">
                                    <div class="progress-container-desc">
                                        <p>
                                        As the student marks the main and elective lessons in full, his BaseLang level will increase until he reaches {{$student->user_level}}.9, after completing all of the level {{$student->user_level}} requirements.
                                        </p>
                                        <p>
                                        To pass to level {{$student->user_level+1}}, the student must pass the level {{$student->user_level}} test.
                                        </p>
                                    </div>
                                </div>

                </div>
            </div>
        </div>
        @endif

        @if($subscriptionType=="dele")
            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">

                            <div class="progress-container-title progress-container-title-left">
                                <h1>Grammar Lessons Progress</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    Here you can see the progress of the student through the grammar lessons of BaseLang DELE. These classes are a deep immersion in specific parts of Spanish grammar.
                                </p>
                            </div>

                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="progress-container-desc">
                                <div class="lessons-summary lessons-summary-title">
                                    <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$levels_summary->grammar->completed}}/{{$levels_summary->grammar->total}}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3">
                                                OVERALL
                                            </div>
                                            <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9">
                                                <div class="bar-wrapper">
                                                    <div class="bar-container">
                                                        <div class="bar-full"></div>
                                                        <div style="width: {{$levels_summary->grammar->total==0?"0%":100*$levels_summary->grammar->completed/$levels_summary->grammar->total}}%" class="bar-fill"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach($levels->grammar as $level)
                                    <div class="lessons-summary">
                                        <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$level->lessons_completed}}/{{$level->lessons_total}}">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3">
                                                    {{$level->name}}
                                                </div>
                                                <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9">
                                                    <div class="bar-wrapper" >
                                                        <div class="bar-container">
                                                            <div class="bar-full"></div>
                                                            <div style="width: {{100*$level->lessons_completed/$level->lessons_total}}%" class="bar-fill"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">

                            <div class="progress-container-title progress-container-title-left">
                                <h1>Skills Improvement Lessons Progress</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    Here you can see the progress of the student through the skills improvement lessons. These are disaggregated by level, but include lessons in the four main areas of language: reading, writing, speaking and listening.
                                </p>
                            </div>

                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="progress-container-desc">
                                <div class="lessons-summary lessons-summary-title">
                                    <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$levels_summary->skills->completed}}/{{$levels_summary->skills->total}}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3">
                                                OVERALL
                                            </div>
                                            <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9">
                                                <div class="bar-wrapper">
                                                    <div class="bar-container">
                                                        <div class="bar-full"></div>
                                                        <div style="width: {{$levels_summary->skills->total==0?"0%":100*$levels_summary->skills->completed/$levels_summary->skills->total}}%" class="bar-fill"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach($levels->skills as $level)
                                    <div class="lessons-summary">
                                        <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$level->lessons_completed}}/{{$level->lessons_total}}">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3">
                                                    {{$level->name}}
                                                </div>
                                                <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9">
                                                    <div class="bar-wrapper" >
                                                        <div class="bar-container">
                                                            <div class="bar-full"></div>
                                                            <div style="width: {{100*$level->lessons_completed/$level->lessons_total}}%" class="bar-fill"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">

                            <div class="progress-container-title progress-container-title-left">
                                <h1>Test-Prep Lessons Progress</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    Here you can see the student's progress through the exam preparation lessons, which are specific to DELE and focus a lot on listening and reading.
                                </p>
                            </div>

                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="progress-container-desc">
                                <div class="lessons-summary lessons-summary-title">
                                    <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$levels_summary->test->completed}}/{{$levels_summary->test->total}}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3">
                                                OVERALL
                                            </div>
                                            <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9">
                                                <div class="bar-wrapper">
                                                    <div class="bar-container">
                                                        <div class="bar-full"></div>
                                                        <div style="width: {{$levels_summary->test->total==0?"0%":100*$levels_summary->test->completed/$levels_summary->test->total}}%" class="bar-fill"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach($levels->test as $level)
                                    <div class="lessons-summary">
                                        <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$level->lessons_completed}}/{{$level->lessons_total}}">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3">
                                                    {{$level->name}}
                                                </div>
                                                <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9">
                                                    <div class="bar-wrapper" >
                                                        <div class="bar-container">
                                                            <div class="bar-full"></div>
                                                            <div style="width: {{100*$level->lessons_completed/$level->lessons_total}}%" class="bar-fill"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 progress-summary-desc">

                            <div class="progress-container-title progress-container-title-left">
                                <h1>Core Lessons Progress</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    Here you will find {{$student->first_name}}'s progress through the main BaseLang lessons.
                                </p>
                            </div>

                        </div>
                        <div class="col-xs-12 col-sm-6 progress-summary-graph">
                            <div class="progress-container-desc">
                                <div class="lessons-summary lessons-summary-title">
                                    <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$levels_summary->completed}}/{{$levels_summary->total}}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3 lesson-progress-title">
                                                OVERALL
                                            </div>
                                            <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9 lesson-progress-bar">
                                                <div class="bar-wrapper">
                                                    <div class="bar-container">
                                                        <div class="bar-full"></div>
                                                        <div style="width: {{$levels_summary->total==0?"0%":100*$levels_summary->completed/$levels_summary->total}}%" class="bar-fill"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach($levels as $level)
                                    <div class="lessons-summary">
                                        <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$level->lessons_completed}}/{{$level->lessons_total}}">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-7 col-md-4 col-lg-3 lesson-progress-title">
                                                    {{$level->name}}
                                                </div>
                                                <div class="col-xs-12 col-sm-5 col-md-8 col-lg-9 lesson-progress-bar">
                                                    <div class="bar-wrapper" >
                                                        <div class="bar-container">
                                                            <div class="bar-full"></div>
                                                            <div style="width: {{100*$level->lessons_completed/$level->lessons_total}}%" class="bar-fill"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="progress-container progress-container-half">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 progress-summary-desc">

                        <div class="progress-container-title progress-container-title-left">
                            <h1>Electives Progress</h1>
                        </div>

                        <div class="progress-container-desc progress-container-desc-left">
                            <p>
                                Here you can see the progress of the student through our electives. The idea is, once you have 100% in one of these elective categories, the student can talk about it without problems!
                            </p>
                            <p>
                                The electives are here to develop the vocabulary and experience by talking about specific topics important to the student.
                            </p>
                        </div>

                    </div>
                    <div class="col-xs-12 col-sm-6 progress-summary-graph">
                        <div class="progress-container-desc">
                            <div class="lessons-summary lessons-summary-title">
                                <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$levels_summary->completed_elective}}/{{$levels_summary->total_elective}}">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-5 lesson-progress-title">
                                            OVERALL
                                        </div>
                                        <div class="col-xs-12 col-sm-5 lesson-progress-bar lesson-progress-bar-electives">
                                            <div class="bar-wrapper">
                                                <div class="bar-container">
                                                    <div class="bar-full"></div>
                                                    <div style="width: {{$levels_summary->total_elective==0?"0%":100*$levels_summary->completed_elective/$levels_summary->total_elective}}%" class="bar-fill"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach($electives as $elective)
                                <div class="lessons-summary">
                                    <div class="container-fluid" data-placement="right" data-toggle="tooltip" title="{{$elective->lessons_completed}}/{{$elective->lessons_total}}">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-5 col-md-5 lesson-progress-title">
                                                {{$elective->name}}
                                            </div>
                                            <div class="col-xs-12 col-sm-5 col-md-7 lesson-progress-bar lesson-progress-bar-electives">
                                                <div class="bar-wrapper" >
                                                    <div class="bar-container">
                                                        <div class="bar-full"></div>
                                                        <div style="width: {{$elective->lessons_total?100*$elective->lessons_completed/$elective->lessons_total:"0"}}%" class="bar-fill"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
--}}
        <div class="progress-container progress-container-half">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-6">

                        <div class="progress-container-title progress-container-title-left">
                            <h1>{{ __('Usage Statistics') }}</h1>
                        </div>

                        <div class="progress-container-desc progress-container-desc-left">
                            <p>
                                {{ __('STATISTICS_DESC_FOR_TEACHER') }}
                            </p>
                        </div>

                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="progress-container-desc">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6">
                                        <div class="statistics-container">
                                            <div class="statistics-title">
                                                {{$statistics->total_classes}}
                                            </div>
                                            <div class="statistics-desc">
                                                {{ __('The student has taken') }} {{$statistics->total_classes}} {{__('total classes so far')}}.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6">
                                        <div class="statistics-container">
                                            <div class="statistics-title">
                                                {{$statistics->user_classes_month}}
                                            </div>
                                            <div class="statistics-desc">
                                                {{ __('The student has taken') }} {{$statistics->user_classes_month}} {{ __('classes so far this month') }}.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-6">
                                        <div class="statistics-container">
                                            <div class="statistics-title">
                                                {{$statistics->user_classes_week}}
                                            </div>
                                            <div class="statistics-desc">
                                                {{ __('The student has taken') }} {{$statistics->user_classes_week}} {{ __('classes so far this week') }}.
                                            </div>
                                        </div>
                                    </div>

                                    {{--
                                    @if(session("current_subscription")!="dele")
                                        <div class="col-xs-12 col-sm-6">
                                            <div class="statistics-container">
                                                <div class="statistics-title">
                                                    {{$statistics->user_level_month}}
                                                </div>
                                                <div class="statistics-desc">
                                                    The student's level increased by {{$statistics->user_level_month}} in the last 30 days.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    --}}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($teachers))
            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">

                            <div class="progress-container-title progress-container-title-left">
                                <h1>{{__("Top 6 Most Used Teachers")}}</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    {{__("Are you curious about the amount of time that the student has spent with different teachers? Discover it here")}}
                                </p>
                            </div>

                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="progress-container-desc">
                                <div class="container-fluid">
                                    <div class="row">
                                        @foreach($teachers as $teacher)
                                            <div class="col-xs-12 col-sm-6">
                                                <div class="used-teacher-container">
                                                    <div class="used-teacher-photo">
                                                        <img src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" />
                                                    </div>
                                                    <div class="used-teacher-title">
                                                        {{$teacher->first_name}}
                                                    </div>
                                                    <div class="used-teacher-desc">
                                                        {{$teacher->total_classes/2}} {{__("hours of class")}}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

       <div class="progress-container progress-container-half">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-6">

                        <div class="progress-container-title progress-container-title-left">
                            <h1>Mini Blog</h1>
                        </div>

                        <div class="progress-container-desc progress-container-desc-left">
                            <p>
                                {{__('MINIBLOG_DESC')}}.
                            </p>
                        </div>

                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="progress-container-desc">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12">
                                        <label>{{ __('Note') }}</label>
                                        <textarea rows="4" class="form-control" placeholder="{{ __('Enter a note if you wish') }}" id="description"></textarea>
                                    </div>
                                    <div style="text-align: center;">
                                        <button style="margin-top: 15px;" id="save_note" type="button" class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="teachers-container">
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            $("#save_note").click(function() {
                if($("#description").val()!="")
                {
                    $.post("{{route("teacher_save_note")}}", {
                        "_token": "{{csrf_token()}}",
                        "user_id": "{{$student->id}}",
                        "description": $("#description").val(),
                    }, function (data) {
                        $("#description").val("");
                        $("#teachers-container").empty();
                        count_skip=0;
                        loadNotes(count_skip,1);
                    });
                }
                else
                {
                    alert("You can not save an empty note");
                }
            });

            var count_skip=0;

            function loadNotes(skip,page) {
                $.get("/teachers_notes/get/{{$student->id}}/"+skip+"/"+page, function( data ) {
                    $("#teachers-container").append(data);
                });

                if(count_skip==0){
                    count_skip+=5;
                }
            }

            $("body").delegate(".load-more-students","click",function () {
                loadNotes(count_skip,1);
                count_skip+=5;
                $(this).remove();
            });

            loadNotes(count_skip,1);
        })
    </script>
@endsection