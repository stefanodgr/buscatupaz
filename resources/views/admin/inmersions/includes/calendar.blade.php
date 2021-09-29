<div id="calendar-inmersion">
    <table id="table-admin-inm">
        <thead>
            @foreach($calendars as $date => $calendar)
                <th id="th-admin-inm"><b>{{strtoupper(DateTime::createFromFormat("Y-m-d", $date)->format("F j, Y"))}}</b></th>
            @endforeach
        </thead>
        <tbody>
            @foreach($final_calendars as $calendar)
                <tr>
                    @foreach($calendar as $cal)
                        @if($cal["inmersion_id"]==null)
                            <td id="td-no-inmersion"></td>
                        @else
                            <td id="td-inmersion">
                                <b>{{ __('Teacher') }}:</b> @if($cal["inmersion"]->teacher) {{$cal["inmersion"]->teacher->first_name}} {{$cal["inmersion"]->teacher->last_name}} @else @endif<br>
                                <b>Student:</b> @if($cal["inmersion"]->student) {{$cal["inmersion"]->student->first_name}} {{$cal["inmersion"]->student->last_name}} ({{$cal["inmersion"]->student->email}}) @else @endif<br>
                                <b>Schedule:</b> @if($cal["inmersion"]->hour_format=="AM") Morning @else Afternoon @endif<br>
                                <b>Finish:</b> {{DateTime::createFromFormat("Y-m-d", $cal["inmersion"]->inmersion_end)->format("F j, Y")}}
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>