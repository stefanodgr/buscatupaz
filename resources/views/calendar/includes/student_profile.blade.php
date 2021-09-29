<div class="student-info">
    <div class="student-info-name">
        @if(file_exists("assets/users/photos/".$student->id.".jpg"))
            <img src="{{ asset('assets/users/photos/'.$student->id.'.jpg?v='.rand()) }}" alt="User Image">
        @else
            <img src="{{ asset('img/user.png') }}" alt="No User Image">
        @endif
        <a href="{{route("get_students_progress",["user_id"=>$student->id])}}">{{$student->first_name}} {{$student->last_name}} <i class="fa fa-eye" aria-hidden="true"></i></a>

    </div>

    <div class="text-right">
        <a href="{{route("get_students_progress",["user_id"=>$student->id])}}"> <span>{{__('Notes for User')}}</span> </a>
    </div>

    <div>

        <div class="info-camp">Email</div>
        <input class="form-control" type="email" value="{{$student->email}}" />
    </div>
    <div>
        <div class="info-camp">Zoom Email</div>
        <input class="form-control" type="email" value="{{$student->zoom_email}}" />
    </div>
    <div>
        <div class="info-camp">Notes</div>
        <textarea class="form-control" name="notes">{{$student->notes}}</textarea>
    </div>
    <div>
        <div class="info-camp">{{ __('About me') }}</div>
        <textarea class="form-control">{{$student->description}}</textarea>
    </div>

    @if($student->describes)
        <div>
            <div class="info-camp">Describes:</div>
            {{$student->describes}}
        </div>
    @endif

    @if($student->motivation)
        <div>
            <div class="info-camp">Motivation:</div>
            {{$student->motivation}}
        </div>
    @endif

    @if($student->getElectives()->count()>0)
        <div>
            <div class="info-camp">Electives</div>
            <ul>
                @foreach($student->getElectives() as $elective)
                    <li>{{$elective->name}}</li>
                @endforeach
            </ul>
        </div>
    @else
        <div>
            <div class="info-camp">Electives</div>
            <div class="no-electives">
                User doesn't have electives
            </div>
        </div>
    @endif
</div>
<div class="student-actions">
    @if($user->is_deleteacher)
        @if($student->dele_sheet)
            <a class="btn btn-block btn-primary" href="{{$student->dele_sheet}}">DELE Sheet</a>
        @endif
    @else
        @if($student->real_sheet)
            <a class="btn btn-block btn-primary" href="{{$student->real_sheet}}">Former Progress Sheet</a>
        @endif
    @endif

    @if($student->electives_sheet)
            <a class="btn btn-block btn-primary" href="{{$student->electives_sheet}}">Progress File RW</a>
    @endif

</div>