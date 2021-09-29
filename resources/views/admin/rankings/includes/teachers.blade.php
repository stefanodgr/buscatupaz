<div class="teachers-container">
    @if(count($teachers)>0)
        @foreach($teachers as $teacher)

            <div class="teacher-container">
                <div class="teacher-title">
                    <img src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" />
                    <div class="teacher-name">
                        {{$teacher->first_name}}
                    </div>
                    <div class="teacher-location">
                        Zoom: {{$teacher->zoom_email}}
                    </div>
                </div>
                <div class="teacher-interets">
                    <h4>Overall Ratings</h4>
                    <span>
                        5 <i class="fa fa-star" aria-hidden="true"></i> ({{$teacher->getEvaluatedStars(5)}})
                    </span>
                    <span>
                        4 <i class="fa fa-star" aria-hidden="true"></i> ({{$teacher->getEvaluatedStars(4)}})
                    </span>
                    <span>
                        3 <i class="fa fa-star" aria-hidden="true"></i> ({{$teacher->getEvaluatedStars(3)}})
                    </span>
                    <span>
                        2 <i class="fa fa-star" aria-hidden="true"></i> ({{$teacher->getEvaluatedStars(2)}})
                    </span>
                    <span>
                        1 <i class="fa fa-star" aria-hidden="true"></i> ({{$teacher->getEvaluatedStars(1)}})
                    </span>
                </div>
                <div class="teacher-interaction">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-no-padding">
                                <div class="rating-title">Average Rating <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Here you will find the average grade of teacher {{$teacher->first_name}}."></i></div>
                                @if($teacher->getEvaluated())
                                    <div class="rating-starts">
                                        @for($i=0; $i < $teacher->getEvaluated(); $i++)<i class="fa fa-star" aria-hidden="true"></i>@endfor
                                        @for($j=0; $j < 5-$teacher->getEvaluated(); $j++)<i class="fa fa-star-o" aria-hidden="true"></i>@endfor
                                    </div>
                                @else
                                    <div class="rating-starts">
                                        @for($j=0; $j < 5; $j++)
                                            <i class="fa fa-star-o" aria-hidden="true"></i>
                                        @endfor
                                    </div>
                                    <div class="rating-title">No student has qualified this teacher so far!</div>
                                @endif
                            </div>
                            <div class="col-xs-12 col-sm-6 favorite-teacher col-no-padding">
                                <a class="btn my-favorite" data-toggle="modal" data-target="#performance-{{$teacher->id}}">Teacher' Performance</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div id="performance-{{$teacher->id}}" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Teacher' Performance</h4>
                    </div>
                    <div class="modal-body">
                        <p id="title-info-perfomance">Last 6 months of {{$teacher->first_name}}</p>
                    </div>
                    <div id="div-canvas-performance"><canvas id="canvas-{{$teacher->id}}"></canvas></div>
                    <div class="modal-body">
                        <p id="title-info-perfomance">Stars</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        @endforeach

        <script>
            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();

                @foreach($teachers as $teacher)
                    
                    var performance = 'canvas-'+'{{$teacher->id}}';

                    var ctx = document.getElementById(performance).getContext('2d');

                    var data = {
                        labels: ["1", "2", "3", "4", "5"],
                        datasets: [
                            {
                              label: "{{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->sub(new DateInterval('P5M'))->format("F")}}",
                              backgroundColor: "rgba(243,229,245 ,0.4)",
                              borderColor: "rgb(156,39,176)",
                              data: ['{{$teacher->fifth_month_one}}', '{{$teacher->fifth_month_two}}', '{{$teacher->fifth_month_three}}', '{{$teacher->fifth_month_four}}', '{{$teacher->fifth_month_five}}'],
                            },
                            {
                              label: "{{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->sub(new DateInterval('P4M'))->format("F")}}",
                              backgroundColor: "rgba(232,234,246 ,0.4)",
                              borderColor: "rgb(63,81,181)",
                              data: ['{{$teacher->fourth_month_one}}', '{{$teacher->fourth_month_two}}', '{{$teacher->fourth_month_three}}', '{{$teacher->fourth_month_four}}', '{{$teacher->fourth_month_five}}'],
                            },
                            {
                              label: "{{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->sub(new DateInterval('P3M'))->format("F")}}",
                              backgroundColor: "rgba(225,245,254 ,0.4)",
                              borderColor: "rgb(3,169,244)",
                              data: ['{{$teacher->third_month_one}}', '{{$teacher->third_month_two}}', '{{$teacher->third_month_three}}', '{{$teacher->third_month_four}}', '{{$teacher->third_month_five}}'],
                            },
                            {
                              label: "{{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->sub(new DateInterval('P2M'))->format("F")}}",
                              backgroundColor: "rgba(224,242,241 ,0.4)",
                              borderColor: "rgb(0,150,136)",
                              data: ['{{$teacher->second_month_one}}', '{{$teacher->second_month_two}}', '{{$teacher->second_month_three}}', '{{$teacher->second_month_four}}', '{{$teacher->second_month_five}}'],
                            },
                            {
                              label: "{{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->sub(new DateInterval('P1M'))->format("F")}}",
                              backgroundColor: "rgba(255,248,225 ,0.4)",
                              borderColor: "rgb(255,193,7)",
                              data: ['{{$teacher->first_month_one}}', '{{$teacher->first_month_two}}', '{{$teacher->first_month_three}}', '{{$teacher->first_month_four}}', '{{$teacher->first_month_five}}'],
                            },
                            {
                              label: "{{DateTime::createFromFormat('Y-m-d',gmdate('Y-m-d'))->format("F")}}",
                              backgroundColor: "rgba(255,235,238 ,0.4)",
                              borderColor: "rgb(244,67,54)",
                              data: ['{{$teacher->current_month_one}}', '{{$teacher->current_month_two}}', '{{$teacher->current_month_three}}', '{{$teacher->current_month_four}}', '{{$teacher->current_month_five}}'],
                            },
                        ]
                    };

                    var myBarChart = new Chart(ctx, {
                      type: 'line',
                      data: data,
                      options: {}
                    });

                @endforeach

            });
        </script>

    @else
        <div class="no-results">
            {{ __('We didn't find results for your search') }}.
        </div>
    @endif
</div>
