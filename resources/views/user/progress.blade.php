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
{{--
        @if(session("current_subscription")!="dele")
            <div class="progress-container progress-container-full">
                <div class="progress-container-title">
                        <div class="c100 p{{$level_progress->user_percentage}} imagina">
                        <span class="image">
                            @if(file_exists("assets/users/photos/".$user->id.".jpg"))
                                <img src="{{ asset('assets/users/photos/'.$user->id.'.jpg?v='.rand()) }}" alt="No User Image">
                            @else
                                <img src="{{ asset('img/user.png') }}" alt="No User Image">
                            @endif
                        </span>
                        <div class="slice">
                            <div class="bar"></div>
                            <div class="fill"></div>
                        </div>
                    </div>


                    <h1>Your BaseLang Level is <span>{{$level_progress->level}}</span></h1>
                </div>

                <div class="progress-container-desc">
                    @if($level_progress->current_level)
                        {!! $level_progress->current_level->meta_description !!}
                    @elseif($level_progress->level==0)
                        These are phrases for getting by in a Spanish-speaking country, as well as coping phrases for when you don’t understand someone. We’ve included some useful vocabulary here as well. There’s nothing to learn grammar-wise, just some things to memorize to get you rolling.
                    @else
                        This is the last level of the core curriculum. Once you finish this level, you are socially fluent. You aren’t necessarily perfect, but you can speak at a high level about a wide variety of topics without having to think too hard or translate much, if at all. This level will take the longest, and will involve fixing lingering mistakes, expanding vocabulary even further, and having a ton of advanced conversations to solidify everything you know.
                    @endif
                </div>

            </div>
        @endif

        @if(session("current_subscription")!="dele")
            @if($level_progress->level!=10)
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


                                    <h1>Level {{$user->user_level}} Progress</h1>

                                    @if($level_progress->core_required!=0)
                                        <div class="content-progress-type">
                                                <div class="content-progress-type-title">
                                                    {{$level_progress->core}}/{{$level_progress->core_required}} Core Lessons completed
                                                </div>
                                            @if($level_progress->next_lesson)
                                                <div class="content-progress-type-next">
                                                    Next Up: <a href="{{route("lesson",["type"=>$level_progress->next_lesson->level->type,"level_slug"=>$level_progress->next_lesson->level->slug,"lesson_slug"=>$level_progress->next_lesson->slug])}}">{{$level_progress->next_lesson->name}}</a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($user->user_level>=3)
                                        <div class="content-progress-type">
                                            <div class="content-progress-type-title">
                                                {{$level_progress->electives}}/{{$user->requiredElectives()}} Electives completed
                                            </div>
                                            @if($level_progress->electives<$user->requiredElectives())
                                                <div class="content-progress-type-next">
                                                    Take any <a href="{{route("electives")}}">elective</a> to fulfill this requirement
                                                </div>
                                            @endif
                                        </div>
                                    @endif


                                </div>

                            </div>
                            <div class="col-xs-12 col-sm-5 col-progress-desc">
                                <div class="progress-container-desc">
                                    <p>
                                    As you mark core lessons and electives complete, your BaseLang level will increase until you hit {{$user->user_level}}.9, after completing all of the level {{$user->user_level}} requirements.
                                    </p>
                                    <p>
                                    To progress to level {{$user->user_level+1}}, pass the level {{$user->user_level}} test.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
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

                                    <h1>Course finished!</h1>

                                    <div class="content-progress-type">
                                        <div class="content-progress-type-title">
                                            {{$user->first_name}}, congratulations for reaching level 10 in BaseLang !. We invite you to continue practicing to continue reinforcing your knowledge. Keep going!
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if(session("current_subscription")=="dele")
            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">

                            <div class="progress-container-title progress-container-title-left">
                                <h1>Grammar Lessons Progress</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    Let’s see your progress through the grammar lessons of BaseLang DELE. These classes are a deep-dive on specific parts of Spanish grammar.
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
                                    Here’s your progress through the skills improvement lessons. These are broken out by level but include lessons on the four main areas of language: reading, writing, speaking, and listening.
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
                                    Here’s your progress through the test-prep lessons, which are DELE-specific and put a heavy focus on listening and reading.
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
                                    Let’s see your progress through the core lessons of BaseLang. Don’t rush through these just to “finish” them, though! It’s better to “know” less, but be better at it.
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
                                Let’s see your progress through our electives. The idea is, once you have 100% in one of these elective categories, you can speak about that topic with no issues!
                            </p>
                            <p>
                                Electives are here to build your vocabulary and experience speaking about the specific topics important to you.
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
                                {{ __('STATISTICS_DESC') }}.
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
                                                {{ __('You have taken') }} {{$statistics->total_classes}} {{ __('total classes so far') }}.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-6">
                                        <div class="statistics-container">
                                            <div class="statistics-title">
                                                {{$statistics->user_classes_month}}
                                            </div>
                                            <div class="statistics-desc">
                                                {{ __('You have taken') }} {{$statistics->user_classes_month}} {{ __('classes so far this month') }}.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-6">
                                        <div class="statistics-container">
                                            <div class="statistics-title">
                                                {{$statistics->user_classes_week}}
                                            </div>
                                            <div class="statistics-desc">
                                                {{ __('You have taken') }} {{$statistics->user_classes_week}} {{ __('classes so far this week') }}.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- @if(session("current_subscription")!="dele")
                                        <div class="col-xs-12 col-sm-6">
                                            <div class="statistics-container">
                                                <div class="statistics-title">
                                                    {{$statistics->user_level_month}}
                                                </div>
                                                <div class="statistics-desc">
                                                    Your level increased by {{$statistics->user_level_month}} in the last 30 days.
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
                                <h1>__("Top 6 Most Used Teachers")</h1>
                            </div>

                            <div class="progress-container-desc progress-container-desc-left">
                                <p>
                                    Curious how much time you’ve spent with different teachers, or who you tend to use the most? Find out here.
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
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        })
    </script>
@endsection