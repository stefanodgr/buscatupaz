@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_locations")}}">
                    Locations <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Users <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_locations")}}" class="btn btn-default">{{ __('Back') }}</a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="admin-list">

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

        <table id="table-list" class="display" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>User</th>
                <th>Rol</th>
                <th>Register Date</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
        </table>


    </div>
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            $('#table-list').DataTable( {
                "ajax": '{{route("get_admin_locations_users")}}',
                "columnDefs": [{ "orderable": false, "targets": -1 }],
                "iDisplayLength": 50,
                "language": {
                    "lengthMenu": "Show _MENU_ users",
                    "info": "Showing _START_ TO _END_ OF _TOTAL_ users",
                    "search":"search:"
                }
            } );
        });
    </script>
@endsection