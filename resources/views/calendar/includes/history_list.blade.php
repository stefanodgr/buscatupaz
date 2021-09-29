<div class="classes-history">
    @foreach($classes as $class)
        <div class="class-history">
            <div class="class-teacher">
                <img src="{{asset("assets/users/photos/".$class->teacher->id.".jpg?v=".rand())}}" alt="{{$class->teacher->first_name}}" />
                {{$class->teacher->first_name}}
            </div>
            <div class="class-datetime">
                {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("F d")}} at {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}@if($class->location=="online"), online @else at the {{$class->location}} School @endif
            </div>
        </div>
    @endforeach

    @if($showMore)
        <div class="load-more-history">
            Show 10 More <i class="fa fa-angle-down" aria-hidden="true"></i>
        </div>
    @endif

</div>