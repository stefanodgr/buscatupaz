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

        <form id="filters_cancellations" method="post" action="{{route("admin_cancellations_filter_table")}}">
            {{ csrf_field() }}
            <div class="filter-actions">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-6 col-sm-3">
                            <label>From</label>
                            <input class="form-control" data-toggle="datepicker" name="from" value="{{$from}}">

                        </div>
                        <div class="col-xs-6 col-sm-3">
                            <label>Till</label>
                            <input class="form-control" data-toggle="datepicker" name="till" value="{{$till}}">
                        </div>

                        <div class="col-xs-6 col-sm-6">
                            <button class="btn btn-primary">Filter</button>
                            <a href="{{route("admin_cancellations")}}" class="btn btn-primary">List</a>
                            <a class="btn btn-primary" href="{{route("admin_cancellations_csv")}}">Download</a>
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

            $.get( "{{route("get_admin_cancellations_table",["from"=>$from,"till"=>$till])}}", function( data ) {
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