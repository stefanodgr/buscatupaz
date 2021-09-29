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
            Electives
        </div>
        <div class="lessons-description">
            There’s only so much “core” Spanish that everyone needs to know. After that, you just need vocab and practice talking about the topics interesting to you. We require that you take Electives to build out your vocabulary, but don’t care which you choose - that’s up to you.
        </div>

        <div class="levels-list">
            @foreach($levels as $level)
                <div class="level {{$level->completed=="0"?"level-item-elective-incomplete":""}}">
                    <a href="{{route("elective_level",["level-slug"=>$level->slug])}}">
                        {{$level->name}}
                    </a>

                    <div class="level-summary">
                        {{$level->completed}}/{{count($level->lessons)}}

                        @if($level->completed!=count($level->lessons))
                            <div class="c100 p{{intval($level->completed*100/count($level->lessons))}} imagina">
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