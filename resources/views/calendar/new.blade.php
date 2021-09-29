@extends("layouts.main")
<meta name="csrf-token" content="{{ csrf_token() }}">
@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
            </div>
            <div class="breadcrumb-actions">
                <div class="breadcrumb-actions-wrapper">
                </div>
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

        <div id="calendar-actions">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="calendar-actions-title">
                            {{ __('Book By') }}
                        </div>
                        <div class="calendar-actions-content">
                            <button id="btn-load-calendar" class="btn btn-default {{(!$teacher_id)?"active":""}}">
                                <img class="image-active" src="{{asset("img/calendar_time_active.png")}}" alt="Calendar By Time"/>
                                <img class="image-inactive" src="{{asset("img/calendar_time.png")}}" alt="Calendar By Time"/>
                                {{ __('Time') }}
                            </button>
                            <button id="btn-load-calendar-teacher" class="btn btn-default {{($teacher_id)?"active":""}}">
                                <img class="image-active" src="{{asset("img/calendar_teacher_active.png")}}" alt="Calendar By Teacher"/>
                                <img class="image-inactive" src="{{asset("img/calendar_teacher.png")}}" alt="Calendar By Teacher"/>
                                {{ __('Teacher') }}
                            </button>
                        </div>

                    </div>
                    <div class="col-sm-12 col-md-6">
                        <div class="calendar-actions-option {{($teacher_id)?"active":""}} calendar-option-teacher">
                            <div class="calendar-actions-title">
                                {{ __('Select your Teacher') }}
                            </div>
                            <div class="calendar-actions-content">
                                <div id="select-teacher-container">

                                    <div class="dropdown show">
                                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <div>
                                                <div class="teacher_name_dropdown">
                                                    @if($teacher_id && $teachers->where("id",$teacher_id)->first())
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

                        @if(!$user->active_locations || ($user->active_locations && gmdate("Y-m-d")<$user->active_locations->date_to_schedule))
                            @if(session("current_subscription")!="dele")
                                <div class="calendar-actions-option {{(!$teacher_id)?"active":""}} calendar-option-show">
                                    <div class="calendar-actions-title">
                                        {{ __('Options') }}
                                    </div>
                                    <div class="calendar-actions-content">
                                        <button class="btn btn-default show-time-continuous">
                                            {{_('Show')}} <i class="fa fa-clock-o" aria-hidden="true"></i> {{ __('When 2+ Consecutive Classes With Same Teacher Are Available') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @else
                            @if($user->active_locations && ($user->active_locations->plan=="medellin_RW" || $user->active_locations->plan=="medellin_RW_1199" || $user->active_locations->plan=="medellin_RW_Lite"))
                                <div class="calendar-actions-option {{(!$teacher_id)?"active":""}} calendar-option-show">
                                    <div class="calendar-actions-title">
                                        {{ __('Options') }}
                                    </div>
                                    <div class="calendar-actions-content">
                                        <button class="btn btn-default show-time-continuous">
                                            {{_('Show')}} <i class="fa fa-clock-o" aria-hidden="true"></i> {{ __('When 2+ Consecutive Classes With Same Teacher Are Available') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>
                </div>
            </div>

        </div>

        <div id="calendar-desc">
            @if(!$user->active_locations || ($user->active_locations && gmdate("Y-m-d")<$user->active_locations->date_to_schedule))
                <p>{{ __('All slots are') }} {{session("current_subscription")!="dele"?"30":"60"}} minutes. {{ __('To book longer sessions, select multiple time slots in a row') }}.</p>
            @else
                <p>{{ __('All slots are') }} @if($user->active_locations && ($user->active_locations->plan=="medellin_RW" || $user->active_locations->plan=="medellin_RW_1199" || $user->active_locations->plan=="medellin_RW_Lite")) 30 @else 60 @endif minutes. {{ __('To book longer sessions, select multiple time slots in a row') }}.</p>
            @endif
            <p>{{ __('Times are shown in your timezone') }}: {{$user->timezone}} ({{DateTime::createFromFormat("H:i:s",gmdate("H:i:s"))->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}) <a href="{{route("profile")}}">change</a></p>
        </div>

        <div id="calendar-classes">
        </div>

        <div id="book-actions">
            <form id="post-calendar" action="{{route('choose_teacher')}}" method="post">
                {{ csrf_field() }}
                @if($location_id)
                    <input type="hidden" name="location_id" value="{{$location_id}}">
                @endif
            </form>
            <button type="button" class="submitcalendar btn btn-primary" disabled>{{ __('Next Step') }}</button>
            <button type="button" class="clearcalendar btn btn-default">{{ __('Clear') }}</button>
        </div>
    </div>

@endsection

@section("scripts")
    <script>
        function filterFunction()
        {
            var input, filter, a, i;
            input = document.getElementById("search-teacher");
            filter = input.value.toUpperCase();
            div = document.getElementById("dropdown-teacher");
            a = div.getElementsByTagName("div");

            for (i = 0; i < a.length; i++)
            {
                if (a[i].innerHTML.toUpperCase().indexOf(filter) > -1)
                {
                    a[i].style.display = "";
                }
                else
                {
                    a[i].style.display = "none";
                }
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            var checkMark = [];
            function loadCalendar(teacher_id){

                $("#calendar-classes table").removeClass("continuous");
                $(".show-time-continuous").removeClass("active");

                if(!teacher_id){
                    @if($location_id)
                    $("#calendar-classes").load("{{route("calendar_in_person_all")}}",function(){
                        $("#book-actions").show();
                    });
                    @else
                    $("#calendar-classes").load("{{route("calendar_all")}}",function(){
                        $("#book-actions").show();
                    });
                    @endif
                } else {
                    @if($location_id)
                    $("#calendar-classes").load("{{route("calendar_in_person_all")}}/"+teacher_id,function(){
                        $("#book-actions").show();
                    });
                    @else
                    $("#calendar-classes").load("{{route("calendar_all")}}/"+teacher_id,function(){
                        $("#book-actions").show();
                    });
                    @endif
                }


            }
            var map = new Map();
            $( "#calendar-classes" ).delegate( "td", "click", function() {
                if($(this).hasClass("no-select")){
                    return true;
                }
                let self = this;
                let tempDate = $(self).attr("time-selected").split(" ")[0];
                let count = 0;
                if(map.has(tempDate)){

                    count = map.get(tempDate)  ;

                    if(!($( this ).hasClass("active"))){
                        count = count + 1 ;
                    }else{
                        count = count - 1;
                    }
                }
                map.set(tempDate,count);
                console.log(map);
                var plan = "{{Session::get('subscription_plan')}}";
                var plan_status = "{{Session::get('subscription_plan_status')}}";
                if(!($( this ).hasClass("active"))){
                    var isPerson = "<?= $is_person ?>";
                    if (plan == "medellin_rw_lite" || (plan == "medellin_rw_1199" && plan_status == "in_trial")) {
                        if(isPerson == "classes_in_person_new" || isPerson == "classes_user_new_teacher") {
                            console.log($(this).attr("time-selected"));
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: 'ajaxupdate',
                                type: 'POST',
                                data: {datetime: $(this).attr("time-selected")},
                                success: function (data) {
                                    if (parseInt(data)+parseInt(count) > 3) {
                                        if(plan == "medellin_rw_lite") {
                                            alert("You have already selected or booked two hours of in-person classes, which is the limited with the Lite plan. Book more online or upgrade to full Real World to remove this in-person class limit.");
                                        } else {
                                            alert("You have already selected or booked two hours of in-person classes, which is the limited when plan in under trial.");
                                        }
                                        count = parseInt(count) - 1;
                                        console.log(count);
                                        map.set(tempDate,count);
                                    } else {
                                        $("#book-actions .submitcalendar").prop('disabled', false);
                                        console.log(self);
                                        $( self ).toggleClass( "active" );
                                    }
                                }
                            });
                        }else{
                            $("#book-actions .submitcalendar").prop('disabled', false);
                            $( self ).toggleClass( "active" );
                        }
                    } else {
                        $("#book-actions .submitcalendar").prop('disabled', false);
                        $( self ).toggleClass( "active" );
                    }
                } else {
                    if($(this).hasClass("no-select")){
                        return true;
                    }
                    $( this ).toggleClass( "active" );
                    if($( "#calendar-classes td.active" ).length>0){
                        $("#book-actions .submitcalendar").prop('disabled', false);
                    } else {
                        $("#book-actions .submitcalendar").prop('disabled', true);
                    }
                }
            });

            $(".submitcalendar").click(function () {

                if($( "#calendar-classes td.active" ).length>0){
                    $.each($( "#calendar-classes td.active" ),function(k,v){

                        if($(".calendar_teacher").length){
                            $("#post-calendar").attr("action","{{route("confirm_classes")}}");
                        }

                        $("#post-calendar").append('<input type="hidden" value="'+$(v).attr("time-selected")+'" name="selected[]"/>');
                    })
                    $("#post-calendar").submit();
                    $("#book-actions .submitcalendar").prop('disabled', true);
                } else {
                    $("#book-actions .submitcalendar").prop('disabled', true);
                }

            });

            $(".clearcalendar").click(function () {
                $("#calendar-classes td.active").removeClass('active');
                $("#book-actions .submitcalendar").prop('disabled', true);
                map.clear();
            });

            $("#btn-load-calendar").click(function () {
                map.clear();
                if($(this).hasClass("active")){
                    return true;
                }

                $(".calendar-actions-content .btn,.calendar-option-teacher").removeClass("active");

                $(this).addClass("active");
                $(".calendar-option-show").addClass("active");

                @if($location_id)
                history.pushState({}, "BaseLang", "{{route("classes_in_person_new")}}");
                @else
                history.pushState({}, "BaseLang", "{{route("classes_new")}}");
                @endif

                loadCalendar();
            });

            $("#btn-load-calendar-teacher").click(function () {
                map.clear();
                if($(this).hasClass("active")){
                    return true;
                }

                $(".calendar-actions-content .btn,.calendar-option-show").removeClass("active");

                $(this).addClass("active");
                $(".calendar-option-teacher").addClass("active");

                @if($location_id)
                history.pushState({}, "BaseLang", "{{route("classes_in_person_new")}}/"+$(".dropdown-menu .teacher-item").attr("teacher-id"));
                @else
                history.pushState({}, "BaseLang", "{{route("classes_new")}}/"+$(".dropdown-menu .teacher-item").attr("teacher-id"));
                @endif

                $(".dropdown.show .dropdown-toggle div").html($(".dropdown-menu .teacher-item").html());
                loadCalendar($(".dropdown-menu .teacher-item").attr("teacher-id"));
            });

            $(".teacher-item").click(function(){
                map.clear();
                @if($location_id)
                history.pushState({}, "BaseLang", "{{route("classes_in_person_new")}}/"+$(this).attr("teacher-id"));
                @else
                history.pushState({}, "BaseLang", "{{route("classes_new")}}/"+$(this).attr("teacher-id"));
                @endif

                $(".dropdown.show .dropdown-toggle div").html($(this).html());
                loadCalendar($(this).attr("teacher-id"));
            });

            $(".show-time-continuous").click(function () {
                $("#calendar-classes table").toggleClass("continuous");
                $(this).toggleClass("active");
            });

            loadCalendar({{$teacher_id?$teacher_id:"false"}});

        })
    </script>
@endsection
