<div class="teachers-container">
    @if(count($teachers)>0)
        @foreach($teachers as $teacher)

            <div class="teacher-container">
                <div class="teacher-title">
                    <img src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" />
                    <div class="teacher-name">
                        {{$teacher->first_name}}
                    </div>
                    <div class="teacher-location">
                        Zoom: {{$teacher->zoom_email}}
                    </div>
                    @if(isset($teacher->youtube_url))
                        <div class="teacher-video-popup" youtube-id="{{$teacher->getYoutubeID()}}">
                            <div class="teacher-video-popup-wrapper">
                                <img src="https://img.youtube.com/vi/{{$teacher->getYoutubeID()}}/0.jpg" />
                                <img src="{{asset("img/play_button.png")}}" class="button-play" alt="Play Video"/>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="teacher-interets">
                    @foreach($teacher->interests as $interest)
                        <span>
                            {{$interest->title}}
                        </span>
                    @endforeach
                </div>
                <div class="teacher-interaction">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-no-padding">
                                <div class="rating-title">My Private Rating  <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="This is for you to help you remember if you liked a teacher or not, so that when you are booking by time, you can choose the teacher you liked most instead of having to remember. This will also be visible for the coordinators when they have to change the teacher for a given class, so that they can not give you a teacher you didn’t like!"></i></div>
                                @if($teacher->getEvaluatedCurrent())
                                    <div class="rating-starts">
                                        @for($i=0;$i<$teacher->getEvaluatedCurrent()->evaluation;$i++)<i class="fa fa-star" aria-hidden="true" teacher-id="{{$teacher->id}}"></i>@endfor
                                        @for($j=0;$j<5-$teacher->getEvaluatedCurrent()->evaluation;$j++)<i class="fa fa-star-o" aria-hidden="true" teacher-id="{{$teacher->id}}"></i>@endfor
                                    </div>
                                @else
                                    <div class="rating-starts">
                                        @for($j=0;$j<5;$j++)
                                            <i class="fa fa-star-o" aria-hidden="true" teacher-id="{{$teacher->id}}"></i>
                                        @endfor
                                    </div>
                                @endif

                            </div>

                            <div class="col-xs-12 col-sm-6 favorite-teacher col-no-padding">
                                @if($user->favorite_teacher==$teacher->id)
                                    <button class="btn my-favorite">
                                        <i class="fa fa-heart" aria-hidden="true"></i> {{ __('My Favorite Teacher') }}
                                    </button>
                                @else

                                    <button class="btn set-favorite" type="button" teacher-name="{{$teacher->first_name}}" teacher-id="{{$teacher->id}}">
                                        <i class="fa fa-heart-o" aria-hidden="true"></i> Set As Favorite Teacher
                                    </button>

                                @endif

                            </div>


                            @if(isset($teacher->youtube_url))
                                <div class="col-xs-12 video-teacher">
                                    <button class="btn teacher-video-popup-responsive" type="button" teacher-name="{{$teacher->first_name}}" teacher-id="{{$teacher->id}}" youtube-id="{{$teacher->getYoutubeID()}}">
                                        <i class="fa fa-play" aria-hidden="true"></i> Video
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endforeach


            <div id="favorite-teacher" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            @if(!isset($user->favorite_teacher_time) || (isset($user->favorite_teacher_time) && $user->favorite_teacher_time->format("Y-m-d")<=gmdate("Y-m-d")))
                                <h4 class="modal-title">Set Favorite Teacher</h4>
                            @else
                                <h4 class="modal-title">Wait {{($user->favorite_teacher_time->format("U")-gmdate("U"))/86400}} More {{($user->favorite_teacher_time->format("U")-gmdate("U"))/86400==1?"Day":"Days"}} First Please</h4>
                            @endif
                        </div>
                        <div class="modal-body">
                            @if(!isset($user->favorite_teacher_time) || (isset($user->favorite_teacher_time) && $user->favorite_teacher_time->format("Y-m-d")<=gmdate("Y-m-d")))
                                <p>This will set <span class="teacher-name"></span> as your “Favorite {{ __('Teacher') }}”, and thus you will be able to book classes with them <b>an extra two days in advance</b>, so that you get first pick at their schedule.</p>
                                <p><i>You can only change your favorite teacher once per week.</i></p>
                            @else
                                <p>You can only change your favorite teacher once a week. You’ll need to wait {{($user->favorite_teacher_time->format("U")-gmdate("U"))/86400}} more {{($user->favorite_teacher_time->format("U")-gmdate("U"))/86400==1?"day":"days"}} before changing to <span class="teacher-name"></span>.</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            @if(!isset($user->favorite_teacher_time) || (isset($user->favorite_teacher_time) && $user->favorite_teacher_time->format("Y-m-d")<=gmdate("Y-m-d")))
                                <form method="post" action="{{route("favorite_teachers")}}">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="teacher_id" class="teacher-id" value="" />
                                    <button type="submit" class="btn btn-primary btn-block">Confirm</button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>

                </div>
            </div>


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

        <script>
            $(document).ready(function () {

                $('[data-toggle="tooltip"]').tooltip();
                $('#video-teacher').on('hidden.bs.modal', function () {
                    var iframe = $('#video-teacher .teacher-video')[0].contentWindow;
                    iframe.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                })
            });

        </script>

    @else
        <div class="no-results">
            {{ __('We didn't find results for your search') }}.
        </div>
    @endif
</div>
