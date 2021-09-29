@extends("layouts.main")

@section("content")

<div class="container-statistics">
    <div class="summary-graph">
        
        <div class="container-data">
        	<h1 class="title-admin-data">Online <b class="information-admin-data">{{$online_active}} ACTIVE + {{$online_hourly}}</b></h1>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-4">
                    	<div class="card-info-data">
                    		<div class="internal-card-info-data">
	                    		<h3><b>Real World</b></h3>
	                    		<a href="{{route('admin_users_online_rw_active')}}"><h3><b class="item-important-data">{{$online_rw}}</b> <b class="information-admin-data">ACTIVE</b></h3></a>
	                    		<h3><b class="item-info-data">{{$online_rw_free_days}}</b> <b class="information-admin-data">FREE DAYS</b></h3>
	                    		<h3><b class="item-info-data">{{$online_rw_paused}}</b> <b class="information-admin-data">PAUSED</b></h3>
                    		</div>
                    	</div>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                    	<div class="card-info-data">
                    		<div class="internal-card-info-data">
	                    		<h3><b>DELE</b></h3>
	                    		<a href="{{route('admin_users_online_dele_active')}}"><h3><b class="item-important-data">{{$online_dele}}</b> <b class="information-admin-data">ACTIVE</b></h3></a>
	                    		<h3><b class="item-info-data">{{$online_dele_free_days}}</b> <b class="information-admin-data">FREE DAYS</b></h3>
	                    		<h3><b class="item-info-data">{{$online_dele_paused}}</b> <b class="information-admin-data">PAUSED</b></h3>
                    		</div>
                    	</div>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                    	<div class="card-info-data">
                    		<div class="internal-card-info-data">
	                    		<h3><b>HOURLY</b></h3>
	                    		<a href="{{route('admin_users_online_hourly_active')}}"><h3><b class="item-important-data">{{$online_hourly}}</b> <b class="information-admin-data">ACTIVE</b></h3></a>
	                    		<h3><b class="item-info-data">{{$online_hourly_free_days}}</b> <b class="information-admin-data">FREE DAYS</b></h3>
	                    		<h3><b class="item-info-data">{{$online_hourly_paused}}</b> <b class="information-admin-data">PAUSED</b></h3>
                    		</div>
                    	</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-data">
        	<h1 class="title-admin-data">Medellin <b class="information-admin-data">{{$medellin_active}} ACTIVE</b></h1>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-4">
                    	<div class="card-info-data">
                    		<div class="internal-card-info-data">
	                    		<h3><b>Real World</b></h3>
	                    		<a href="{{route('admin_users_medellin_rw_mo_active')}}"><h3><b class="item-important-data">{{$medellin_rw_mo}}</b> <b class="information-admin-data">ACTIVE/MO</b></h3></a>
	                    		<h3><b class="item-important-data">{{$medellin_rw_wk}}</b> <b class="information-admin-data">ACTIVE/WK</b></h3>
	                    		<h3><b class="item-info-data">{{$medellin_rw_free_days}}</b> <b class="information-admin-data">FREE DAYS</b></h3>
	                    		<h3><b class="item-info-data">{{$medellin_rw_paused}}</b> <b class="information-admin-data">PAUSED</b></h3>
	                    		<h3><b class="item-info-data">{{$medellin_rw_start_soon}}</b> <b class="information-admin-data">START SOON</b></h3>
                    		</div>
                    	</div>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                    	<div class="card-info-data">
                    		<div class="internal-card-info-data">
	                    		<h3><b>DELE</b></h3>
	                    		<a href="{{route('admin_users_medellin_dele_mo_active')}}"><h3><b class="item-important-data">{{$medellin_dele_mo}}</b> <b class="information-admin-data">ACTIVE/MO</b></h3></a>
	                    		<h3><b class="item-important-data">{{$medellin_dele_wk}}</b> <b class="information-admin-data">ACTIVE/WK</b></h3>
	                    		<h3><b class="item-info-data">{{$medellin_dele_free_days}}</b> <b class="information-admin-data">FREE DAYS</b></h3>
	                    		<h3><b class="item-info-data">{{$medellin_dele_paused}}</b> <b class="information-admin-data">PAUSED</b></h3>
	                    		<h3><b class="item-info-data">{{$medellin_dele_start_soon}}</b> <b class="information-admin-data">START SOON</b></h3>
                    		</div>
                    	</div>
                    </div>
                    <div class="col-xs-12 col-sm-4">
                    	<div class="card-info-data">
                    		<div class="internal-card-info-data">
	                    		<h3><b>SM</b></h3>
	                    		<a href="{{route('admin_users_medellin_sm_active')}}"><h3><b class="item-important-data">{{$medellin_sm_active}}</b> <b class="information-admin-data">ACTIVE</b></h3></a>
	                    		<h3><b class="item-info-data">{{$medellin_sm_start_soon}}</b> <b class="information-admin-data">START SOON</b></h3>
                    		</div>
                    	</div>
                    </div>
                </div>
            </div>
        </div>

    </div>  
</div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

        });
    </script>
@endsection
