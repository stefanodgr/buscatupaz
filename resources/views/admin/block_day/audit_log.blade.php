@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_block_day")}}">
                    Blocked Days <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Audit Logs <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_block_day")}}" class="btn btn-default">Cancel</a>
            </div>
        </div>

        <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="edit_action">

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
            <h1>Logs Details</h1>
            <div class="table-responsive" style="width: 150%;margin-left: -25%;">
                        <div class="admin-section">
                            <br>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <th>Admin Mail</th>
                                    <th>Action</th>
                                    <th>Old Data</th>
                                    <th>New Data</th>
                                    <th>Date of change</th>
                                </thead>
                                <tbody>
                                    @foreach($block_days_logs as $logs)
                                    <tr>
                                        <td>{{$logs->email}}</td>
                                        <td>{{$logs->action}}</td>
                                        <td>{!!$logs->old_data!!}</td>
                                        <td>{!!$logs->new_data!!}</td>
                                        <td>{{$logs->created_at}}</td>
                                    </tr>
                                    @endforeach 
                                  
                                </tbody>
                            </table>
                        </div>
                </div>
        </div>
    @endif
@endsection