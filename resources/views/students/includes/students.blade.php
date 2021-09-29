<div class="teachers-container">
    @if(count($students)>0)
        @foreach($students as $student)

            <div class="teacher-container">
                <div class="teacher-title">
                    <img src="{{asset("assets/users/photos/".$student->id.".jpg?v=".rand())}}" alt="{{$student->first_name}}" />
                    <div class="teacher-name">
                        {!! $student->isNew()?'<i class="fa fa-rocket" aria-hidden="true"></i>':""!!}
                        <a href="{{route("get_students_progress",["user_id"=>$student->id])}}">{{$student->first_name}} (Lv: {{$student->user_level}}) <i class="fa fa-eye" aria-hidden="true">{{__('Notes for User')}}</i></a>
                    </div>
                    <div class="teacher-location">
                        Email: {{$student->email}}
                    </div>

                    @if($student->zoom_email)
                        <div class="teacher-location">
                            Zoom Email: {{$student->zoom_email}}
                        </div>
                    @endif


                    @if($student->description)
                        <div class="teacher-description">
                            <b>{{ __('About me') }}:</b> {{$student->description}}
                        </div>
                    @endif

                    <div class="teacher-description">
                        <b>Elective:</b>
                        @if($student->getElectives()->count()>0)
                            @foreach($student->getElectives() as $elective)
                                <div class="elective-title">
                                    {{$elective->name}}
                                </div>
                            @endforeach
                        @else
                            <div class="no-electives">
                                User doesn't have electives
                            </div>
                        @endif
                    </div>



                </div>

                <div class="teacher-interaction">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 upgrade-student no-padding">
                                    <button target="_blank" class="btn btn-primary updagrade_button" userid="{{$student->id}}">
                                        Upgrade Level
                                    </button>
                                    <button target="_blank" class="btn btn-danger downgrade_button" userid="{{$student->id}}">
                                        Downgrade Level
                                    </button>
                            </div>
                            <div class="col-xs-12 col-sm-6 favorite-teacher no-padding">

                                @if(!$user->is_deleteacher && $student->real_sheet)
                                    <a target="_blank" class="btn" href="{{$student->real_sheet}}">
                                        Student Progress Sheet
                                    </a>
                                @elseif(!$user->is_deleteacher)
                                    <a class="btn">
                                        No progress sheet
                                    </a>
                                @endif

                                @if($user->is_deleteacher && $student->dele_sheet)
                                    <a target="_blank" class="btn" href="{{$student->dele_sheet}}">
                                        Student Progress Sheet
                                    </a>
                                @elseif($user->is_deleteacher)
                                    <a class="btn">
                                        No progress sheet
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach

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

        <form method="post" action="{{route("student_up_level")}}">
            {{ csrf_field() }}
        </form>

        <div id="level_modify" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <form action="" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" value="" name="student_id" id="student_id">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to continue?
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary">Continue</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    <script>
        $(".upgrade-student button").click(function () {
            if($(this).hasClass("downgrade_button")){
                $("#level_modify form").attr("action","{{route("student_down_level")}}");
                $("#level_modify .modal-title").text("Downgrade Level");
            } else {
                $("#level_modify form").attr("action","{{route("student_up_level")}}");
                $("#level_modify .modal-title").text("Upgrade Level");
            }


            $("#level_modify #student_id").val($(this).attr("userid"));
            $("#level_modify").modal();
        })
    </script>

</div>



