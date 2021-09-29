@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("classes_new")}}">
                    {{ __('Select Times') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    {{ __('Choose Teachers') }}
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

        <div class="calendar-container">
            <div class="calendar-container-title">
                {{ __('Choose Teachers') }}
            </div>
            <div class="calendar-container-desc">
                {{ __('For each of the times you selected, you can now choose which available teacher you’d like to have it with') }}.
            </div>

            <div class="calendar-container-classes">
                @foreach($classes as $k=>$class)
                    <div class="class-container">
                        <div class="class-title">
                            @if(DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d H:i:s",$k)->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d"))
                                {{__('Today')}},
                            @elseif(DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->add(new \DateInterval('P1D'))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d H:i:s",$k)->format("Y-m-d"))
                                {{__('Tomorrow')}},
                            @endif
                            {{DateTime::createFromFormat("Y-m-d H:i:s",$k)->setTimezone(new DateTimeZone($user->timezone))->format("F d")}}
                            <span>{{DateTime::createFromFormat("Y-m-d H:i:s",$k)->setTimezone(new DateTimeZone($user->timezone))->format("- h:iA")}}</span>

                        </div>

                        <div class="class-teachers">
                            @foreach($class as $j=>$teacher)
                                <div class="class-teacher-wrapper">
                                    <div class="class-teacher {{$j==0 && $first_hour==$k?"previewActive":""}} {{$j==0?"active":""}}" class-info="{{DateTime::createFromFormat("Y-m-d H:i:s",$k)->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d H:i:s")}},{{$teacher->id}}">
                                        <img src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" />
                                        <div class="teacher-name">
                                            {{$teacher->first_name}}
                                            <div class="teacher-evaluation">
                                                @if(isset($user->favorite_teacher) && $user->favorite_teacher==$teacher->id)
                                                    <i class="fa fa-heart" aria-hidden="true"></i> {{ __('My Favorite Teacher') }}
                                                @elseif($teacher->getEvaluatedCurrent())
                                                    @for($i=0;$i<$teacher->getEvaluatedCurrent()->evaluation;$i++)
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    @endfor
                                                @else
                                                    Unrated
                                                @endif
                                            </div>
                                        </div>
                                        {{--
                                        <div class="teacher-description">

                                            <div class="teacher-description-wrapper">
                                                <div class="teacher-description-content">
                                                    <div class="teacher-description-title">
                                                        {{$teacher->first_name}}
                                                        <span>{{$teacher->location}}</span>
                                                    </div>
                                                    <div class="teacher-description-summary">
                                                        <div class="teacher-strengths">
                                                            <span class="teacher-strength">
                                                                {{$teacher->teaching_style}} Teaching Style
                                                            </span>
                                                            <span class="teacher-strength">
                                                                Strongest With {{$teacher->strongest_with}}
                                                            </span>

                                                            @if(($teacher->interests))
                                                                @foreach($teacher->interests as $interest)
                                                                    <span class="teacher-strength">
                                                                        {{$interest->title}}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        @if(isset($teacher->youtube_url))
                                                            <div class="teacher-video">
                                                                <iframe src="https://www.youtube.com/embed/{{$teacher->getYoutubeID()}}?showinfo=0&rel=0&enablejsapi=1" frameborder="0" allowfullscreen></iframe>
                                                            </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    --}}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <div id="book-actions" class="choose-action">
            <form id="post-calendar" action="{{route('confirm_classes')}}" method="post">
                {{ csrf_field() }}
                @if($location_id)
                    <input type="hidden" name="location_id" value="{{$location_id}}">
                @endif
            </form>
            <button type="button" class="submitcalendar btn btn-primary">{{ __('Next Step') }}</button>
            <a href="{{route("classes_new")}}" type="button" class="btn btn-default">{{ __('Back') }}</a>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            function pauseVideo(element) {
                var iframe = element[0].contentWindow;
                iframe.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            }

            $(".submitcalendar").click(function () {
                $.each($(".class-teachers"),function(k,v){
                    $("#post-calendar").append('<input type="hidden" value="'+$(this).find(".active").attr("class-info")+'" name="selected[]"/>');
                })
                $("#post-calendar").submit();
                $("#book-actions .submitcalendar").prop('disabled', true);
            });


            $(".class-teacher").mouseleave(function(){
                $(this).removeClass("hovered");
                pauseVideo($(this).find("iframe"));
            });

            $(".class-teacher-wrapper").click(function () {
                $(this).parent().find(".class-teacher.active").removeClass("active");
                $(".class-teacher").removeClass("previewActive");
                $(".class-teacher").removeClass("lastpreviewActive");
                $(this).find(".class-teacher").addClass("active");
                $(this).find(".class-teacher").addClass("previewActive");

            });

            $(".class-teacher-wrapper").hover(function () {
                $(".class-teacher.previewActive").addClass("lastpreviewActive");
                $(".class-teacher.previewActive").removeClass("previewActive");
                $(this).find(".class-teacher").addClass("previewActive");

            },function () {

                if(!$(this).find(".class-teacher").hasClass("lastpreviewActive")){
                    return true;
                }

                //$(".class-teacher.lastpreviewActive").addClass("previewActive");
                //$(".class-teacher.lastpreviewActive").removeClass("lastpreviewActive");

            });


            $("iframe").mouseenter(function(){
                $(this).parents(".class-teacher").addClass("hovered");
            });
        })
    </script>
@endsection