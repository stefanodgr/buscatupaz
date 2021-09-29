@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))

        <div class="imagina-breadcrumb {{(isset($lesson->externalurl) && !empty($lesson->externalurl) && $lesson->externalurl)?"externalurl":""}}">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("electives")}}">
                    Electives <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item" href="{{route("elective_level",["level_slug"=>$level->slug])}}">
                    {{$level->name}} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    {{$lesson->name}} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions {{($user_lesson && $user_lesson->pivot->completed==1)?"complete":""}}">
                <div class="breadcrumb-actions-wrapper">
                    <button class="btn btn-default" id="change-lesson-status">
                        <span class="incomplete">
                            Mark Complete
                            <img src="{{asset("img/lesson_no_completed.png")}}" alt="Level Completed"/>
                            <img class="lesson-summary-transition" src="{{asset("img/loading-circle.svg")}}">
                        </span>
                        <span class="complete">
                            Marked Complete
                            <img src="{{asset("img/lesson_completed.png")}}" alt="Level No Completed"/>
                            <img class="lesson-summary-transition" src="{{asset("img/loading-circle.svg")}}">
                        </span>
                    </button>
                    @if(isset($lesson->externalurl) && !empty($lesson->externalurl) && $lesson->externalurl)
                        <a href="{{$lesson->externalurl}}" class="btn btn-primary" target="_blank">Study in Memrise</a>
                    @endif
                </div>
            </div>

        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}} lesson-main-content-wrapper" id="lessons">

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

        <div class="lesson-title">
            {{$lesson->name}}
        </div>
        <div class="lesson-description">
            {!! $lesson->description !!}
        </div>


        @if(file_exists("assets/lessons/pdf/".$lesson->id.".pdf"))
            <div class="lesson-pdf">
                <iframe src="{{asset("library/pdf/index.html")}}#{{asset("assets/lessons/pdf/".$lesson->id.".pdf?".$lesson->updated_at->format("U"))}}"></iframe>
            </div>
        @endif

        @if($lesson->hasHomeworks())
            <div class="homework">

                <div class="homework-title">Homework</div>
                @if($lesson->hasTextHomework())
                    <div class="homework-text">
                        <label>Text Response:</label>
                        @if($user_lesson && isset($user_lesson->pivot->homework))
                            <div class="text-response">
                                {{$user_lesson->pivot->homework}}
                            </div>
                        @else
                            <textarea></textarea>
                        @endif


                    </div>
                @endif
                @if($lesson->hasAudioHomework())
                    <div class="homework-audio">
                        <label>Voice recording:</label>
                        @if(!file_exists("assets/homeworks/user_audio/".$lesson->id."_".$user->id.".wav"))
                            <div>
                                <button class="btn btn-default start">Begin Recording</button>
                                <button class="btn btn-default redo">Redo Recording</button>
                                <button class="btn btn-default stop">Stop Recording</button>
                                <div class="audio-container">
                                    <audio class="audio-preview" src="" controls>
                                    </audio>
                                </div>
                                <div class="log-container">
                                    Recording...
                                </div>
                            </div>
                        @else
                            <div class="audio-container active">
                                <audio class="audio-preview" src="{{asset("assets/homeworks/user_audio/".$lesson->id."_".$user->id.".wav")}}" controls>
                                </audio>
                            </div>
                        @endif
                    </div>
                @endif
                @if(($lesson->hasAudioHomework() && !file_exists("assets/homeworks/user_audio/".$lesson->id."_".$user->id.".wav")) || ($lesson->hasTextHomework() && (!$user_lesson || !isset($user_lesson->pivot->homework))))
                    <div class="submit-homework">
                        <button class="btn btn-primary submit-homework-button">Submit (You Can’t change This After Submitting)</button>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/recorder.js")}}"></script>
    <script>
        $(document).ready(function(){

            var recorder;
            var audio_context;
            var audioBlob=false;

            function lessonchange(element){
                element.unbind( "click");
                element.addClass("process");
                $('#change-lesson-status span.complete').css("display", "none");
                $('#change-lesson-status span.incomplete').css("display", "inline");
                $.post( "{{route("elective_complete")}}",{"_token":"{{csrf_token()}}","lesson":"{{$lesson->id}}"}, function( data ) {
                    if(data.lesson_state){
                        $('#change-lesson-status span.incomplete').css("display", "none");
                        $('#change-lesson-status span.complete').css("display", "inline");
                    }
                    element.removeClass("process");

                    element.click(function(){
                        lessonchange($(this));
                    })

                });


            }

            function upload() {
                var fd = new FormData();
                if(audioBlob){
                    fd.append('audio_data', audioBlob);
                }

                fd.append("text_homework", $(".homework-text textarea").val()?$(".homework-text textarea").val():"");
                fd.append("lesson", "{{$lesson->id}}");
                fd.append("_token", "{{csrf_token()}}");
                $.ajax({
                    type: 'POST',
                    url: '{{route("elective_homework_upload")}}',
                    data: fd,
                    processData: false,
                    contentType: false
                }).done(function(data) {
                    location.reload();
                });

            }

            @if(($lesson->hasAudioHomework() && !file_exists("assets/homeworks/user_audio/".$lesson->id."_".$user->id.".wav")) || ($lesson->hasTextHomework() && (!$user_lesson || !isset($user_lesson->pivot->homework))))
                function startUserMedia(stream) {
                    var input = audio_context.createMediaStreamSource(stream);
                    recorder = new Recorder(input);
                }

                function startRecording() {
                    $(".homework-audio").removeClass("recoded");
                    recorder && recorder.record();
                    $(".homework-audio").addClass("recoding");
                    //button.disabled = true;
                    //button.nextElementSibling.disabled = false;
                }

                function stopRecording() {
                    recorder && recorder.stop();
                    $(".homework-audio").removeClass("recoding");
                    $(".homework-audio").addClass("recoded");
                    createPreviewAudio();
                    recorder.clear();
                }

                function createPreviewAudio() {
                    recorder && recorder.exportWAV(function(blob) {
                        var url = URL.createObjectURL(blob);
                        audioBlob=blob;
                        $(".audio-preview").attr("src",url);
                    });
                }

                try {
                    window.AudioContext = window.AudioContext || window.webkitAudioContext;
                    navigator.getUserMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
                    window.URL = window.URL || window.webkitURL;

                    audio_context = new AudioContext;

                } catch (e) {
                    //alert('No web audio support in this browser!');
                }

                navigator.getUserMedia({audio: true}, startUserMedia, function(e) {});

                $(".homework-audio button.stop").click(function () {
                    stopRecording();
                });

                $(".homework-audio button.start,.homework-audio button.redo").click(function () {
                    startRecording();
                });
            @endif

            $(".submit-homework-button").click(function () {
                upload();
            });

            $("#change-lesson-status").click(function () {
                lessonchange($(this));
            })



        })
    </script>
@endsection