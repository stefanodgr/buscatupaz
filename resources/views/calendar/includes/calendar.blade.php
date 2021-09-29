<table class="{{$teacher?"calendar_teacher":"calendar_time"}}">
    <thead>
    <tr>
        @foreach($days as $k=>$day)
            <th>{{DateTime::createFromFormat("Y-m-d",$k)->format("D, M d")}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @if($max)
        @for($i=0;isset(array_values($days[$max])[$i]);$i++)
            <tr>
                @foreach(array_values($days) as $k=>$hour)
                    @if($teacher && isset($hour[$i]))
                        <td time-selected="{{array_keys($days)[$k]}} {{$hour[$i]->time.':00'}},{{$teacher}}">
                    @elseif(isset($hour[$i]))
                        <td time-selected="{{array_keys($days)[$k]}} {{$hour[$i]->time}}" teachers="{{implode(',',$hour[$i]->teacher)}}">
                    @else
                        <td class="no-select">
                            @endif
                            @if(isset($hour[$i]))
                                {{DateTime::createFromFormat("H:i",$hour[$i]->time)->format("h:iA")}}

                                @if($hour[$i]->continuous)
                                    <i class="time-continuous fa fa-clock-o" aria-hidden="true"></i>
                                @endif
                            @endif
                        </td>
                        @endforeach
            </tr>
        @endfor
    @else
        <tr>
            <td colspan="{{count($days)}}" class="no-select">Classes are not available for this teacher</td>
        </tr>
    @endif
    </tbody>
</table>
