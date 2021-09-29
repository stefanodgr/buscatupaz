@extends("layouts.main")

@section("content")
    <div class="main-content-wrapper" id="dashboard">


            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    {{ __('Scheduled Classes') }}
                </div>

                @if(count($classes)>0)
                    <div class="content-dashboard-desc">
                        <p>These are your upcoming classes.</p>
                    </div>
                @else
                    <div class="content-dashboard-desc">
                        <p>You don't have upcoming classes.</p>
                    </div>
                @endif

                <div class="content-dashboard-classes">

                    @foreach($classes as $class)
                        <div class="class-confirm">
                            <img src="{{asset("assets/users/photos/".$class->teacher->id.".jpg?v=".rand())}}" alt="{{$class->teacher->first_name}}" />
                            <div class="teacher_name">
                                {{$class->teacher->first_name}}
                            </div>
                            <div class="teacher_time">
                                @if($class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d"))
                                    {{ __('Today at') }} {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                                @elseif($class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->add(new DateInterval('P1D'))->format("Y-m-d"))
                                    {{ __('Tomorrow at') }} {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                                @else
                                    {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("l d")}} at {{$class->getClassDateTime()->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                                @endif
                            </div>
                            <div class="launch-class" class-id="{{$class->id}}">

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>




    </div>


@endsection

@section("scripts")
    <script>
        $(document).ready(function () {


        })
    </script>
@endsection