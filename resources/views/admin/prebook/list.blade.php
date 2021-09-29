@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item">
                    Prebooks <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a class="btn btn-primary" href="{{route("admin_prebooks_availability_teachers")}}" class="btn btn-default">Availability of Teachers</a>
                <a class="btn btn-primary" href="{{route("admin_prebooks_csv")}}">Download</a>
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

        <table id="table-list" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>Expiration Date</th>
                <th></th>
            </tr>
            </thead>
        </table>

    </div>
@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/recorder.js")}}"></script>
    <script>
        $(document).ready(function() {
            $('#table-list').DataTable( {
                "ajax": '{{route("get_admin_prebooks")}}',
                "columnDefs": [{ "orderable": false, "targets": -1 }],
                "iDisplayLength": 50,
                "language": {
                    "lengthMenu": "Show _MENU_ prebooks",
                    "info": "Showing _START_ to _END_ of _TOTAL_ prebooks",
                    "search":"search:"
                }
            } );
        });
    </script>
@endsection