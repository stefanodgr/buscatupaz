@extends("layouts.main")

@section("content")

    <div class="main-content-wrapper" id="calendar">
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

        <div id="calendar-actions">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="calendar-actions-option active calendar-option-teacher">
                            <div class="calendar-actions-title">
                                {{ __('Select your Teacher') }}
                            </div>
                            <div class="calendar-actions-content">
                                <div id="select-teacher-container">
                                    <div class="dropdown show">
                                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <div>
                                                <div class="teacher_name_dropdown">
                                                    @if($teacher_id)
                                                        {{$teachers->where("id",$teacher_id)->first()->first_name}}
                                                    @else
                                                        {{$teachers->first()->first_name}}
                                                    @endif
                                                </div>
                                                @if(isset($user->favorite_teacher) && ($user->favorite_teacher==$teacher_id || (!$teacher_id && $user->favorite_teacher==$teachers->first()->id)))
                                                    (<i class="fa fa-heart" aria-hidden="true"></i> {{ __('My Favorite Teacher') }})
                                                @elseif($teachers->where("id",$teacher_id)->first() && $teachers->where("id",$teacher_id)->first()->getEvaluatedCurrent())
                                                    @for($i=0;$i<$teachers->where("id",$teacher_id)->first()->getEvaluatedCurrent()->evaluation;$i++)<i class="fa fa-star" aria-hidden="true"></i>@endfor
                                                @elseif($teachers->first()->getEvaluatedCurrent())
                                                    @for($i=0;$i<$teachers->first()->getEvaluatedCurrent()->evaluation;$i++)<i class="fa fa-star" aria-hidden="true"></i>@endfor
                                                @endif
                                            </div>
                                            <span><i class="fa fa-angle-down" aria-hidden="true"></i></span>
                                        </a>
                                        <div id="dropdown-teacher" class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <input class="input-search-teacher" type="text" placeholder="{{ __('Search teacher') }}..." id="search-teacher" onkeyup="filterFunction()">
                                            <br>
                                            @foreach($teachers as $k=>$teacher)
                                                <div class="teacher-item" teacher-id="{{$teacher->id}}">
                                                    <div class="teacher_name_dropdown">
                                                        {{$teacher->first_name}}
                                                    </div>
                                                    @if(isset($user->favorite_teacher) && $user->favorite_teacher==$teacher->id)
                                                        (<i class="fa fa-heart" aria-hidden="true"></i> {{ __('My Favorite Teacher') }})
                                                    @elseif($teacher->getEvaluatedCurrent())
                                                        @for($i=0;$i<$teacher->getEvaluatedCurrent()->evaluation;$i++)<i class="fa fa-star" aria-hidden="true"></i>@endfor
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
				        <div id="calendar-desc">
				            <p>{{ __('All slots are') }} {{session("current_subscription")!="dele"?"30":"60"}} minutes. To prebook longer sessions, select multiple time slots in a row. {{ __('Times are shown in your timezone') }}: {{$user->timezone}} ({{DateTime::createFromFormat("H:i:s",gmdate("H:i:s"))->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}) <a href="{{route("profile")}}">change</a></p>
				        </div>
                    </div>
                    <div class="col-sm-12 col-md-6">
				        <div id="calendar-desc">
				            <p>Remember, only 25% of a teacher’s total time each day can be prebooked. This is so that all students can still get a chance at booking all teachers.</p>
				            <br>
				            <p>The number of slots that can be prebooked for a particular day of the week is shown in the circle next to the day.</p>
				        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="calendar-classes">
        </div>

        <div id="book-actions">
            <a href="{{route("prebook")}}" class="btn btn-default">{{ __('Back') }}</a>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        function filterFunction() {
            var input, filter, a, i;
            input = document.getElementById("search-teacher");
            filter = input.value.toUpperCase();
            div = document.getElementById("dropdown-teacher");
            a = div.getElementsByTagName("div");
            
            for(i = 0; i < a.length; i++) {
                if(a[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } 
                else {
                    a[i].style.display = "none";
                }
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            
            function loadCalendar(teacher_id) {
                $("#calendar-classes table").removeClass("continuous");
                $(".show-time-continuous").removeClass("active");

                $("#calendar-classes").load("{{route("prebook_all")}}/"+teacher_id,function() {
                    $("#book-actions").show();
                });
            }

            $(".teacher-item").click(function() {
                history.pushState({}, "BaseLang", "{{route("get_prebook_availability")}}/"+$(this).attr("teacher-id"));
                location.reload();
            });

            loadCalendar({{$teacher_id?$teacher_id:$teachers->first()->id}});

        })
    </script>
@endsection