@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("lessons_type",["type"=>$level->type])}}">
                    @if($level->type=="real")
                        Core Lessons
                    @elseif($level->type=="intros")
                        Intros
                    @elseif($level->type=="grammar")
                        Grammar
                    @elseif($level->type=="skills")
                        Skills Improvement
                    @elseif($level->type=="test")
                        Test-Prep
                    @elseif($level->type=="sm")
                        GL Lessons
                    @endif
                        <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    {{$level->name}}
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="lessons">

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
            {{$level->name}}
        </div>
        <div class="lessons-description">
            {!! $level->meta_description !!}

        </div>

        <div class="levels-list">
            @foreach($level->lessons->where("enabled",1) as $lesson)
                <div class="level lesson-item">
                    <a href="{{route("lesson",["type"=>$level->type,"level_slug"=>$level->slug,'lesson_slug'=>$lesson->slug])}}">
                        {{$lesson->name}}
                    </a>

                    <div class="lesson-summary {{$lesson->completed?"active":""}}" lesson_id="{{$lesson->id}}">

                        <span class="lesson-summary-completed-span">MARK INCOMPLETE</span>
                        <img class="lesson-summary-completed" src="{{asset("img/lesson_completed.png")}}" alt="Level Completed"/>


                        <span class="lesson-summary-incompleted-span">MARK COMPLETE</span>
                        <img class="lesson-summary-incompleted" src="{{asset("img/lesson_no_completed.png")}}" alt="Level Completed"/>


                        <img class="lesson-summary-transition" src="{{asset("img/loading-circle.svg")}}">

                    </div>

                </div>
            @endforeach
        </div>
    </div>
@endsection

@section("scripts")
    <script>
        $(document).ready(function(){
            function lessonchange(element){
                element.unbind( "click");
                element.addClass("process")
                element.removeClass("active");
                $.post( "{{route("lesson_complete")}}",{"_token":"{{csrf_token()}}","lesson":element.attr("lesson_id")}, function( data ) {
                    if(data.lesson_state){
                        element.addClass("active");
                    }
                    element.removeClass("process");

                    element.click(function(){
                        lessonchange($(this));
                    })

                });


            }

            $(".lesson-summary").click(function(event){
                lessonchange($(this));
            })
        })
    </script>
@endsection