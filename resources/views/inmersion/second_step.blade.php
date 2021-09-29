@extends("layouts.inmersion")

@section("content")
    
    <h1 class="text-left title-pick margin-title">Pick Your Teacher</h1>

    <p class="text-inmersion inm-margin">Below are the teachers available to start on {{DateTime::createFromFormat("Y-m-d", $week->inmersion_start)->format("F d")}}th. Pick which one you’d like here.</p><br>

    @foreach($teachers as $key => $teacher)

        <div class="thumbnail content-card" time-selected="{{$week->inmersion_start}},{{$week->inmersion_end}},{{$week->hour_format}},{{$teacher->id}}">
            <div class="caption">
                <div class="row">
                    <div class="col-xs-12 col-sm-2 text-center img-content teacher-info">
                        @if(file_exists("assets/users/photos/".$teacher->id.".jpg"))
                            <img class="img-teacher-inm" src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" data-toggle="modal" data-target="#modal-photo-{{$teacher->id}}"/>
                        @else
                            <img class="img-teacher-inm" src="{{ asset('img/user.png') }}" alt="No User Image">
                        @endif
                    </div>
                    <div class="col-xs-12 col-sm-8 teacher-info">
                        <br>
                        <h4 class="teacher-name-inm title-yt-inm">{{ $teacher->first_name }} {{ $teacher->last_name }}</h4>
                        <p class="text-inmersion p-yt-inm">{{ $teacher->description }}</p>
                    </div>
                    <div class="col-xs-12 col-sm-2 div-youtube">
                        @if($teacher->getYoutubeID())
                            <div class="teacher-video-popup div-yt-inm" youtube-id="{{$teacher->getYoutubeID()}}">
                                <div class="teacher-video-popup-wrapper wrapper-yt-inm">
                                    <img class="img-yt-inm" src="https://img.youtube.com/vi/{{$teacher->getYoutubeID()}}/0.jpg" />
                                    <img src="{{asset("img/play_button.png")}}" class="button-play-inm" alt="Play Video"/>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-photo-{{$teacher->id}}" class="modal fade" role="dialog">
            <div class="modal-dialog" id="modal-photos-teacher">
                <!-- Modal content-->
                <img id="img-photos-immersion" src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" />
            </div>
        </div>

    @endforeach

    <hr class="hr-third-step margin-hr-inm">


    <br><button class="btn btn-primary btn-next-step" disabled>{{ __('Next Step') }}</button><br><br><br>

    <div id="video-teacher" class="modal modal-video fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-body">
                    <iframe class="teacher-video" src="" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <form id="post-calendar" action="{{route('your_basic_info')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="location_id" value="{{$location->id}}">
    </form>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $("body").delegate(".teacher-video-popup,.teacher-video-popup-responsive","click",function () {
                $('#video-teacher .teacher-video').attr("src","https://www.youtube.com/embed/"+$(this).attr("youtube-id")+"?showinfo=0&enablejsapi=1&autoplay=1&rel=0");
                $('#video-teacher').modal('show');
            });

            $('#video-teacher').on('hidden.bs.modal', function () {
                var iframe = $('#video-teacher .teacher-video')[0].contentWindow;
                iframe.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
            })

            $(".thumbnail").click(function () {
                var withClasses = document.getElementsByClassName("thumb-active");
                for (var i = 0; i<withClasses.length; i++) {
                    withClasses[i].classList.remove("thumb-active");
                }

                $(this).toggleClass("thumb-active");
                $(".btn-next-step").prop('disabled', false);
            });

            $(".btn-next-step").click(function () {
                var count = $(".thumb-active").length;

                if(count>=1) {
                    
                    $(".btn-next-step").prop('disabled', true);

                    $.each($(".thumb-active"),function(k,v) {
                        $("#post-calendar").append('<input type="hidden" value="'+$(v).attr("time-selected")+'" name="selecteds[]"/>');
                    })

                    $("#post-calendar").submit();

                } else {
                    $(".btn-next-step").prop('disabled', true);
                }

            });

            $(".main-menu-responsive-bars").click(function () {
                $(this).toggleClass("active");
                $("#menu,.main-menu").toggleClass("active");
            });

        });
    </script>
@endsection