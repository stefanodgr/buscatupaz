@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item">
                    Pauses <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_users")}}" class="btn btn-default">{{ __('Back') }}</a>
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
                <th>User</th>
                <th>Start date of the pause</th>
                <th>Scheduled resume date</th>
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
                "ajax": '{{route("get_admin_pauses")}}',
                "columnDefs": [{ "orderable": false, "targets": -1 }],
                "iDisplayLength": 50,
                "language": {
                    "lengthMenu": "Show _MENU_ pauses",
                    "info": "Showing _START_ TO _END_ OF _TOTAL_ pauses",
                    "search":"search:"
                }
            } );
        });
    </script>
@endsection