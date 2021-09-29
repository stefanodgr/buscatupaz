@extends("layouts.inmersion")

@section("content")
    
    @if($errors->any())
        <div class="margin-title">
            @foreach ($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {{$error}}
                </div>
            @endforeach
        </div>
    @endif

    <h1 class="text-left title-pick margin-title">Pick Your Dates</h1>
    <p class="text-inmersion inm-margin">Click on the Monday you’d like to start your program. The program goes for four weeks, ending on the final Friday.</p>
    @if(isset($location) && $location->time_message)
        <p class="text-inmersion">{{$location->time_message}}</p>
    @endif

    <br>
    <div id="calendar-inmersion">
        <table>
            <thead>
                @foreach($calendars as $date => $calendar)
                    <th><b>{{strtoupper(DateTime::createFromFormat("Y-m", $date)->format("F Y"))}}</b></th>
                @endforeach
            </thead>
            <tbody>
                @foreach($final_calendars as $calendar)
                    <tr>
                        @foreach($calendar as $cal)
                            @if($cal["start_week"]==null)
                                <td class="no-select"></td>
                            @else

                                @if($cal["status"]==1)
                                    <td time-selected="{{$cal['start_week']}},{{$cal['week_end']}},{{$cal['format']}}"><b>{{DateTime::createFromFormat("Y-m-d", $cal["start_week"])->format("M d")}} - {{DateTime::createFromFormat("Y-m-d", $cal["week_end"])->format("M d")}}</b> - @if($cal["format"]=="AM") Morning @else Afternoon @endif</td>
                                @else
                                    <td class="no-select busy-week" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="This week is sold out!"><b>{{DateTime::createFromFormat("Y-m-d", $cal["start_week"])->format("M d")}} - {{DateTime::createFromFormat("Y-m-d", $cal["week_end"])->format("M d")}}</b> - @if($cal["format"]=="AM") Morning @else Afternoon @endif</td>
                                @endif

                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <br><br><br>

    <p class="text-inmersion" id="total-price">Total price: $0</p>
    <p class="text-inmersion" id="fifty-percent">50% deposit to pay today: $0</p>
    <br>
    <button class="btn btn-primary btn-next-step" disabled>{{ __('Next Step') }}</button>
    <br><br><br><br><br><br>

    <form id="post-calendar" action="{{route('pick_your_teacher')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="location_id" value="{{$location->id}}">
    </form>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $("#calendar-inmersion").delegate("td", "click", function() {
                if($(this).hasClass("no-select")){
                    return true;
                }

                var withClasses = document.getElementsByClassName("active");
                for (var i = 0; i<withClasses.length; i++) {
                    withClasses[i].classList.remove("active");
                }

                $(this).toggleClass("active");
                $(".btn-next-step").prop('disabled', false);
                $("#total-price").text("Total price: {{$amount}}$");
				
				var arr = $(this);
				var selectedSlotDate = arr[0]['outerHTML'];
				var date = selectedSlotDate.slice(19, 29);
				var theDate = new Date(date);
				theDate.setDate(theDate.getDate()-8);
				var today = new Date();
				if (theDate > today){
					$("#fifty-percent").text("50% deposit to pay today: {{$amount/2}}$");
					$("#fifty-percent").show();
				}
				else{
					$("#fifty-percent").hide();
				}		
            });

            $(".btn-next-step").click(function () {
                if($("#calendar-inmersion td.active" ).length>0) {

                    $(".btn-next-step").prop('disabled', true);
                    
                    $.each($("#calendar-inmersion td.active" ),function(k,v) {
                        $("#post-calendar").append('<input type="hidden" value="'+$(v).attr("time-selected")+'" name="selecteds[]"/>');
                    })

                    $("#post-calendar").submit();

                } else {
                    $(".btn-next-step").prop('disabled', true);
                }
            });

            $('[data-toggle="tooltip"]').tooltip();

            $(".main-menu-responsive-bars").click(function () {
                $(this).toggleClass("active");
                $("#menu,.main-menu").toggleClass("active");
            });
                
        })
    </script>
@endsection