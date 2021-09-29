@extends("layouts.main")

@section("content")

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

            @if($user->location_id && !$user->read_prebook)
                <div class="book-class content-dashboard">
                    <div class="content-dashboard-title">
                        Prebook
                    </div>
                    <div class="calendar-container-desc" id="text-content-primary-pre">
                        <p class="text-content-prebook">With prebook, you can schedule with any teacher ahead of time.</p>
                        <p class="text-content-prebook">For instance, if you prebook 9am on Monday with Carlos, that class will automatically be booked for you before the schedule is released. So not only do you get the teacher you want at the time you want, but you don’t have to manually book the class.</p>
                        <p class="text-content-prebook">Prebook is an extra fee for online students, but as a member of the BaseLang {{$location?ucfirst($location->name).' ':''}}School, you have it included! However, you can only prebook your in-person teachers, not online teachers.</p>
                    </div>
                    <div class="content-dashboard-actions">
                        <a href="{{route('read_prebook')}}" class="btn btn-primary btn-block btn-pre">Got it! Don’t show this again.</a>
                    </div>
                </div>

                <br>
            @endif

            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    Your Prebook Schedule
                </div>
                <div class="calendar-container-desc" id="text-content-primary-pre">
                    <p class="text-content-prebook">Below are all the classes you currently have set to prebook.</p>
                    <p class="text-content-prebook">If you no longer want a time to be booked every week, cancel it. If you only need to cancel a specific class, cancel it once it shows up in your <a href="{{route("classes")}}">{{ __('Scheduled Classes') }}</a> section.</p>
                </div>

                @if(count($prebooks)>0)
                    <div class="classes-confirm" style="margin-left: 20px; margin-right: 20px;">
                        @foreach($prebooks as $prebook)
                            <div class="class-confirm">
                                <img src="{{asset("assets/users/photos/".$prebook->teacher->id.".jpg?v=".rand())}}" alt="{{$prebook->teacher->first_name}}" />
                                <div class="teacher_name">
                                    {{$prebook->teacher->first_name}} <span class="teacher_email">Zoom: {{$prebook->teacher->zoom_email}}</span>
                                </div>
                                <div class="teacher_time">
                                    {{$prebook->user_time->format("l, d F Y")}} at {{$prebook->user_time->format("h:ia")}}
                                </div>
                                <div class="cancel-class" class-id="{{$prebook->id}}">
                                    Cancel
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else

                @endif
            </div>

            <br>

            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    @if(count($prebooks)==0) Prebook Your First Class @elseif(count($prebooks)==$user->buy_prebooks->first()->hours) Maximum Prebook Limit @else Prebook More Classes @endif
                </div>
                <div class="content-dashboard-desc" id="content-text-info">
                    <p>With your plan, you can have up to {{$user->buy_prebooks->first()->hours}} hours per week of classes prebooked. You are currently using {{count($prebooks)*0.5}} out of those {{$user->buy_prebooks->first()->hours}} hours.</p>
                </div>
                @if(count($prebooks)==0 || count($prebooks)<$user->buy_prebooks->first()->hours*2)
                    <div class="content-dashboard-actions">
                        <a href="{{route("prebook_new")}}" class="btn btn-primary btn-block btn-pre">@if(count($prebooks)==0) Prebook Your First Class @else Prebook More Classes @endif</a>
                    </div>
                @endif
            </div>

        </div>

        <form id="cancel_class" action="{{route('cancel_prebook')}}" method="post">
            <input type="hidden" id="classtocancel" name="prebook" value="0">
            {{ csrf_field() }}
        </form>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $(".cancel-class").click(function () {
                $("#classtocancel").val($(this).attr("class-id"));
                $("#cancel_class").submit();
            });
        });
    </script>
@endsection