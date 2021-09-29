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

        <form id="filters_cancellations" method="post" action="{{route("admin_cancellations_filter")}}">
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
                            <a class="btn btn-primary" href="{{route("admin_cancellations_table")}}">Table</a>
                            <a class="btn btn-primary" href="{{route("admin_cancellations_csv")}}">Download</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <table id="table-list" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Student</th>
                <th>Reason</th>
                <th>Description</th>
                <th>Date and Time</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/recorder.js")}}"></script>
    <script>
        $(document).ready(function() {

            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            $('#table-list').DataTable( {
                "ajax": '{{route("get_admin_cancellations",["from"=>$from,"till"=>$till])}}',
                "columnDefs": [{ "orderable": false }],
                "iDisplayLength": 50,
                "language": {
                    "lengthMenu": "Show _MENU_ clases",
                    "info": "Showing _START_ to _END_ of _TOTAL_ cancellations",
                }
            } );
        });
    </script>
@endsection