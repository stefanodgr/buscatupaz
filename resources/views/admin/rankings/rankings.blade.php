@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
            </div>
            <div class="breadcrumb-actions">
                <div class="breadcrumb-actions-wrapper">
                </div>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="teachers">
        @if($errors->any())
            @foreach ($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!! $error !!}
                </div>
            @endforeach
        @endif

        @if(session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{ session('message_info') }}
            </div>
        @endif

        <h1>Rankings</h1>
        <br>
        <div id="select_specific_rating">
            <a class="btn btn-primary btn-block" href="{{route("rankings_teachers_csv")}}">Download CSV</a>
        </div>
        <br><br>
        <div id="select_specific_rating">
            <label>Location filter</label>
            <select class="form-control" id="specific_location">
                <option value="all">All locations</option>
                <option value="none">No location</option>
                @foreach($locations as $location)
                    <option value="{{$location->id}}">{{ucwords(strtolower($location->name))}}</option>
                @endforeach
            </select>
        </div>
        <br><br>
        <div id="select_specific_rating">
            <label>Filtered by a specific rating</label>
            <select class="form-control" id="specific_rating">
                <option value="all">All ratings</option>
                <option value="5">5 stars</option>
                <option value="4">4 stars</option>
                <option value="3">3 stars</option>
                <option value="2">2 stars</option>
                <option value="1">1 star</option>
            </select>
        </div>

        <div id="teachers-container">
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            $("#specific_rating").change(function () {
                ShowSpecificRating();
            });

            $("#specific_location").change(function () {
                ShowSpecificRating();
            });

            function loadTeachers(){
                $("#teachers-container").load("{{route("get_teachers_rankings")}}",function(){

                });
            }

            function ShowSpecificRating(){
                var route = "/admin/rankings/filter/"+$("#specific_rating").val()+"/"+$("#specific_location").val();

                $("#teachers-container").load(route,function(){

                });
            }

            loadTeachers();
        })
    </script>
@endsection