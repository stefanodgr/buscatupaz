@extends("layouts.main")

@section("content")
    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrump":""}}" id="lessons">
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

        <div class="lessons-title">
            @if($type=="real")
                Core Lessons
            @elseif($type=="intros")
                Intros
            @elseif($type=="grammar")
                Grammar
            @elseif($type=="skills")
                Skills Improvement
            @elseif($type=="test")
                Test-Prep
            @elseif($type=="sm")
                Grammarless Lessons
            @endif
        </div>
        <div class="lessons-description">
            @if($type=="real")
                The building blocks. These are the core lessons that will give you a strong base (all the way up to advanced grammar) in Spanish. Includes the most common words that everyone needs to know.
            @elseif($type=="intros")
                In these introductory classes, you’ll learn how our DELE program works, as well as a detailed explanation how the official tests are setup, for those taking it.
            @elseif($type=="grammar")
                The building blocks. These are the grammar lessons that will give you a strong base (all the way up to advanced grammar) in Spanish. Includes the most common words that everyone needs to know.
            @elseif($type=="skills")
                The building blocks. These are the skills improvement lessons that will give you a strong base (all the way up to advanced grammar) in Spanish. Includes the most common words that everyone needs to know.
            @elseif($type=="test")
                The building blocks. These are the test-prep lessons that will give you a strong base (all the way up to advanced grammar) in Spanish. Includes the most common words that everyone needs to know.
            @elseif($type=="sm")
                The building blocks. These are the core lessons that will give you a strong base (all the way up to advanced grammar) in Spanish. Includes the most common words that everyone needs to know.
            @endif
        </div>

        <div class="levels-list">
            @foreach($levels as $level)
                <div class="level {{$level->level_order>$user->user_level?"level-item-incomplete":""}}">
                    <a href="{{route("level",["type"=>$level->type,"level-slug"=>$level->slug])}}">
                        {{$level->name}}
                    </a>

                    <div class="level-summary">
                        {{$level->completed}}/{{count($level->lessons->where("enabled",1))}}

                        @if($level->completed!=count($level->lessons->where("enabled",1)))
                            <div class="c100 p{{intval($level->completed*100/count($level->lessons->where("enabled",1)))}} imagina">
                                <span></span>
                                <div class="slice">
                                    <div class="bar"></div>
                                    <div class="fill"></div>
                                </div>
                            </div>
                        @else
                            <img src="{{asset("img/lesson_completed.png")}}" alt="Level Completed"/>
                        @endif

                    </div>

                </div>
            @endforeach
        </div>
    </div>
@endsection