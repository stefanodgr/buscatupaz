@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item">
                    Users <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            @if(Route::currentRouteName()=="admin_users")
                <div class="breadcrumb-actions">
                    <a class="btn btn-primary" href="{{route("admin_users_csv")}}">Download</a>
                    <a href="{{route("admin_pauses")}}" class="btn btn-default">Pauses</a>
                    <a href="{{route("admin_users_create")}}" class="btn btn-default">Create</a>
                </div>
            @endif
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
                <th>Role</th>
                <th>Register Date</th>
                <th>Status</th>
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
            var route = '{{route("get_admin_users")}}';

            @if(Route::currentRouteName()=="admin_users_online_rw_active")
                route = '{{route("get_admin_users_filter",["type"=>"online_rw"])}}';
            @elseif(Route::currentRouteName()=="admin_users_online_dele_active")
                route = '{{route("get_admin_users_filter",["type"=>"online_dele"])}}';
            @elseif(Route::currentRouteName()=="admin_users_online_hourly_active")
                route = '{{route("get_admin_users_filter",["type"=>"online_hourly"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_rw_mo_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_rw_mo"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_rw_wk_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_rw_wk"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_rw_1199_mo_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_rw_1199_mo"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_rw_lite_mo_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_rw_lite_mo"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_dele_mo_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_dele_mo"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_dele_wk_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_dele_wk"])}}';
            @elseif(Route::currentRouteName()=="admin_users_medellin_sm_active")
                route = '{{route("get_admin_users_filter",["type"=>"medellin_sm"])}}';
            @endif

            $('#table-list').DataTable( {
                "ajax": route,
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
