<table class="calendar_teacher">
    <thead>
        <tr>
            @foreach($calendar as $k=>$day)
                <th class="title-head title-head-{{$k}}" @if($day->percentage==0) data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="This teacher already has 25% of their time for this day of the week prebooked, so prebooking more time for this day isn’t possible." @endif>
                    {{DateTime::createFromFormat("U",strtotime('monday this week'))->add(new DateInterval("P".($k-1)."D"))->format("D")}}
                    <div class="div-circle-table">{{$day->percentage<0?0:$day->percentage}}</div>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @for($i=0;$i<$max;$i++)
            <tr>
                @foreach($calendar as $k=>$day)

                    @if(isset($day->hours[$i]))
                        <td class="day-{{$k}} {{in_array($day->hours[$i],$day->ownhours)?'active':''}}" time-day="{{$k}}" time-selected="{{$k}},{{$day->hours[$i]}},{{$teacher}}" @if($day->percentage==0) data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="This teacher already has 25% of their time for this day of the week prebooked, so prebooking more time for this day isn’t possible." @endif>{{$day->hours[$i]}}</td>
                    @else
                        <td class="no-select" @if($day->percentage==0) data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="This teacher already has 25% of their time for this day of the week prebooked, so prebooking more time for this day isn’t possible." @endif></td>
                    @endif
                @endforeach
            </tr>            
        @endfor
    </tbody>
</table>

<script>
    $(document).ready(function () {
        @if($limit_prebook)
            var maxClasses=parseInt("{{($limit_prebook->hours*2)-count($user->prebooks)+$ownhours}}")//Gold or silver
        @else
            var maxClasses=0;
        @endif
        var maxClass=[];
        var freeClasses=[]
        @foreach($calendar as $k=>$day)
            maxClass[{{$k}}] = {{$day->percentage<0?0:$day->percentage}}
            freeClasses[{{$k}}] = {{count($day->ownhours)}};
        @endforeach



        $("#calendar-classes td").click(function() {
            var day = $(this).attr("time-day");
            if($(this).hasClass("no-select")) {
                return true;
            }

            if($("#calendar-classes td.active").length>=maxClasses && !$(this).hasClass("active")){
                return true;
            }

            if(maxClass[day]==0 && !$(this).hasClass("active")){
                return true;
            }

            $(this).toggleClass("active");
            //it was inactive
            if($(this).hasClass("active")){
                maxClass[day]--;
                freeClasses[day]++;
            } else {
                maxClass[day]++;
                freeClasses[day]--;
            }
            $(".title-head-"+day+" .div-circle-table").text(maxClass[day])


            if($( "#calendar-classes td.active" ).length>0){
                $("#book-actions .submitcalendar").prop('disabled', false);
            } else {
                $("#book-actions .submitcalendar").prop('disabled', true);
            }
        });

        if($( "#calendar-classes td.active" ).length>0){
            $("#book-actions .submitcalendar").prop('disabled', false);
        } else {
            $("#book-actions .submitcalendar").prop('disabled', true);
        }

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
