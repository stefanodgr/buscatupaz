@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            <div class="breadcrumb-actions">

            </div>
        </div>
    @endif
    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="admin-list">

        @if($errors->any())
            @foreach ($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!! $error !!}
                </div>
            @endforeach
        @endif


        @if (session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{ session('message_info') }}
            </div>
        @endif

        <form id="filters_classes" method="post" action="{{route("admin_classes_filter_table")}}">
            {{ csrf_field() }}
            <div class="filter-actions">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-6 col-sm-2">
                            <label>From</label>
                            <input class="form-control" data-toggle="datepicker" name="from" value="{{$from}}">

                        </div>
                        <div class="col-xs-6 col-sm-2">
                            <label>Till</label>
                            <input class="form-control" data-toggle="datepicker" name="till" value="{{$till}}">
                        </div>
                        <div class="col-xs-6 col-sm-2">
                            <label>{{ __('Teacher') }}</label>
                            <select class="form-control" name="teacher">
                                <option value="0">All</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{$teacher->id}}" {{$filter_teacher && $filter_teacher==$teacher->id?"selected":""}}>{{$teacher->first_name}} {{$teacher->last_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xs-6 col-sm-2">
                            <label>Student</label>
                            <select class="form-control" name="student">
                                <option value="0">All</option>
                                @foreach($students as $student)
                                    <option value="{{$student->id}}" {{$filter_student && $filter_student==$student->id?"selected":""}}>{{$student->first_name}} {{$student->last_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xs-6 col-sm-3">
                            <button class="btn btn-primary">Filter</button>
                            <a href="{{route("admin_classes")}}" class="btn btn-primary">List</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div id="table-container">
            <div id="pivottable"></div>
        </div>
    </div>
@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/recorder.js")}}"></script>
    <script>
        $(document).ready(function() {

            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            $.get( "{{route("get_admin_classes_table",["from"=>$from,"till"=>$till,"teacher"=>$filter_teacher,"student"=>$filter_student])}}", function( data ) {
                var pivot=data.data;


                $("#pivottable").pivotUI(pivot,
                    {
                        cols: ["day"],
                        rows: ["time"],
                        rendererName: "Heatmap",
                    });

            });




        });
    </script>
@endsection