@extends("layouts.main")

@section("content")
    
    <div class="main-content-wrapper">

        <h1>Immersions</h1>

        <div class="container-fluid">
            <div class="row div-inmersions">
                <div>
                    <label>Location</label>
                    <select id="location" class="form-control select-admin-inmersions">
                        @foreach($locations as $location)
                            <option value="{{$location->id}}">{{ucwords(strtolower($location->name))}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

    </div>

    <div id="calendar-inmersions"></div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
           
            function loadCalendar(location_id){
                $("#calendar-inmersions").load("/admin/immersions/locations/"+location_id,function(){});
            }

            $("#location").change(function () {
                loadCalendar($("#location").val());
            });

            loadCalendar('{{$locations->first()->id}}');

        });
    </script>
@endsection