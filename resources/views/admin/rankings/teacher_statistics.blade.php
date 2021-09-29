@extends("layouts.main")

@section("content")
<div class="container-statistics">
    <div class="summary-graph">
        <div class="summary-graph-title">Teachers favorites</div>
        <div class="div-content">
            <div class="row">
                <div class="col-sm-4 text-center">
                    <h5><b>History of favorite teachers</b></h5>
                    <a href="{{route("admin_history_teachers_favorites_csv")}}" class="btn btn-primary">Generate CSV</a>
                </div>
                <div class="col-sm-4 text-center">
                    <h5><b>Current score of favorite teachers</b></h5>
                    <a href="{{route("admin_teachers_favorites_csv")}}" class="btn btn-primary">Generate CSV</a>
                </div>
                <div class="col-sm-4 text-center">
                    <h5><b>Subscriptions status</b></h5>
                    <a href="{{route("admin_subscriptions_status_csv")}}" class="btn btn-primary">Generate CSV</a>
                </div>
            </div>
        </div>
        <br>
        <div class="canvas-container">
            <div class="summary-graph-title">Location filter
                <select id="select_location" class="form-control">
                    <option value="all">All locations</option>
                    <option value="no_location">No location</option>
                    @foreach($locations as $location)
                        <option value="{{$location->id}}">{{ucwords(strtolower($location->name))}}</option>
                    @endforeach
                </select>
            </div>
            <div id="container-canvas"></div>
        </div>
        <a href="{{route('admin_coordinator_rankings')}}" class="btn btn-primary btn-block btn-pre" style="max-width: 250px; margin: auto;">Summary</a>
        <br><br>
    </div>  
</div>
@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $("#select_location").change(function () {
                loadTeachersFavorites($(this).val());               
            });
            function loadTeachersFavorites(location_id){
                $("#container-canvas").load("/admin/load_teachers_favs/"+location_id,function(){});
            }
            loadTeachersFavorites('all');
        });
    </script>
@endsection