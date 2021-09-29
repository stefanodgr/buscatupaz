@if($class && $user->getCurrentRol()->name=="teacher")
    @if($nowClass)
        You have a class with {{$class->student->first_name}}. right now.
        <div class="alarm-actions">

        </div>

    @else
        You have a class with {{$class->student->first_name}}. starting in {{$leftminutes}} minutes.
    @endif

    <div class="alarm-actions alarm-actions-progress">

        @if(!$user->is_deleteacher && $class->student->electives_sheet)
            <a href="{{$class->student->electives_sheet}}">Progress Sheet</a>
        @elseif(!$user->is_deleteacher)
            <a>No Progress Sheet</a>
        @endif

        @if($user->is_deleteacher && $class->student->dele_sheet)
            <a href="{{$class->student->dele_sheet}}">Progress Sheet</a>
        @elseif($user->is_deleteacher)
            <a>No Progress Sheet</a>
        @endif
    </div>

@elseif($class)
    @if($nowClass)
        You have a class with {{$class->teacher->first_name}}. right now.
        <div class="alarm-actions">

        </div>

    @else
        You have a class with {{$class->teacher->first_name}}. starting in {{$leftminutes}} minutes.
    @endif
@endif




