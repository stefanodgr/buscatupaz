@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_prebooks")}}">
                    Prebooks <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Availability of Teachers <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="calendar">

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!!$error!!}
                </div>
            @endforeach
        @endif

        @if(session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{session('message_info')}}
            </div>
        @endif

        <div class="calendar-container">
            <div class="calendar-container-title">
                Availability of Teachers
            </div>
            <div class="calendar-container-desc">
                <div id="select_specific_rating">
                    <label>Filtered by a specific day</label>
                    <select class="form-control" id="specific_rating">
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="7">Sunday</option>
                    </select>
                </div>
            </div>
            <div id="teachers-container"></div>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $("#specific_rating").change(function () {
                showTeachers();
            });

            function showTeachers(){
                var route = "/admin/prebooks/check_availability/"+$("#specific_rating").val();
                $("#teachers-container").load(route,function(){});
            }

            showTeachers();

        });
    </script>
@endsection