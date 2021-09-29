@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_pauses")}}">
                    Pauses <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Edit <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="edit_action">

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

            <h1>Edit Pause</h1>

            <form>
                {{ csrf_field() }}
                <input type="hidden" name="pause_id" value="{{$edit_pause->id}}"/>

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }} of Student</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Email</label>
                                <input class="form-control" value="{{$edit_pause->user->email}}" readonly/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Name</label>
                                <input class="form-control" value="{{$edit_pause->user->first_name}} {{$edit_pause->user->last_name}}" readonly/>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Start date of the pause</label>
                                <input class="form-control" value="{{$start_date_pause}}" readonly/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Scheduled resume date</label>
                                <input class="form-control" value="{{$edit_pause->activation_day}}" readonly/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin_actions">
                    @if($start_date_pause && gmdate("Y-m-d") > $start_date_pause)
                        <a class="btn btn-success" data-toggle="modal" data-target="#resume">Resume</a>
                    @endif
                    <a class="btn btn-primary" data-toggle="modal" data-target="#extend">Extend</a>
                    @if($edit_pause->user->getCurrentSubscription() && $edit_pause->user->getCurrentSubscription()->status=="cancelled")
                        <a class="btn btn-danger" data-toggle="modal" data-target="#cancel">Cancel</a>
                    @endif
                    <a class="btn btn-default" href="{{route("admin_pauses")}}">{{ __('Back') }}</a>
                </div>

            </form>
        </div>

    @endif

    <div id="resume" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Resume Paused Subscription</h4>
                </div>
                <div class="modal-body">
                    <p style="text-align: center;">Are you sure to reactivate the user's subscription {{$edit_pause->user->email}} right now?</p>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-success btn-block" href="{{route("admin_restart_subscription_now",["pause_id"=>$edit_pause->id])}}">Resume</a>
                    <a type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <div id="extend" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Extend Paused Subscription</h4>
                </div>
                <div class="modal-body">
                    <p style="text-align: center;">Are you sure to extend the user's subscription {{$edit_pause->user->email}} right now?, if so, select a new date.</p>
                </div>
                <div class="modal-footer">
                    <form action="{{route("admin_restart_subscription_after")}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="pause_id" value="{{$edit_pause->id}}"/>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    <label>Scheduled resume date</label>
                                    <input style="text-align: center;" class="form-control" value="{{$edit_pause->activation_day}}" disabled/>
                                    <br>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    <label>New scheduled resume date</label>
                                    <select class="form-control" name="activation_day">
                                        <option value="{{DateTime::createFromFormat("Y-m-d",$edit_pause->activation_day)->add(new DateInterval("P7D"))->format("Y-m-d")}}">1 week ({{DateTime::createFromFormat("Y-m-d",$edit_pause->activation_day)->add(new DateInterval("P7D"))->format("F j, Y")}})</option>
                                        <option value="{{DateTime::createFromFormat("Y-m-d",$edit_pause->activation_day)->add(new DateInterval("P28D"))->format("Y-m-d")}}">1 month ({{DateTime::createFromFormat("Y-m-d",$edit_pause->activation_day)->add(new DateInterval("P28D"))->format("F j, Y")}})</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary btn-block">Extend</button>
                        <a type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="cancel" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Cancel Paused Subscription</h4>
                </div>
                <div class="modal-body">
                    <p style="text-align: center;">Are you sure to cancel the user's subscription pause {{$edit_pause->user->email}} right now?</p>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-danger btn-block" href="{{route("admin_pause_undo_now",["user_id"=>$edit_pause->user->id])}}">Cancel</a>
                    <a type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</a>
                </div>
            </div>
        </div>
    </div>

@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/redactor.js")}}"></script>
@endsection