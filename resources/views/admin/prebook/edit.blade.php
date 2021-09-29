@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_prebooks")}}">
                    Prebook <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Edit <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a class="btn btn-primary" data-toggle="modal" data-target="#modal-prebooks">Student's Prebook</a>
                <a href="{{route("admin_prebooks_new", ["user_id"=>$edit_buy_prebook->student->id])}}" class="btn btn-default">New Prebook</a>
                <a href="{{route("admin_prebooks")}}" class="btn btn-default">Cancel</a>
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

            <h1>Edit Prebook</h1>

            <form action="{{route("admin_prebooks_update")}}" method="post" >
                {{ csrf_field() }}
                <input type="hidden" name="buy_prebook_id" value="{{$edit_buy_prebook->id}}"/>

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Name</label>
                                <input class="form-control" type="text" value="{{$edit_buy_prebook->student->first_name}} {{$edit_buy_prebook->student->last_name}}" readonly>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Email</label>
                                <input class="form-control" type="text" value="{{$edit_buy_prebook->student->email}}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Type</label>
                                <select class="form-control" name="type">
                                    <option value="silver" {{$edit_buy_prebook->type=="silver"?"selected":""}}>Silver</option>
                                    <option value="gold" {{$edit_buy_prebook->type=="gold"?"selected":""}}>Gold</option>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Status</label>
                                <select class="form-control" name="status">
                                    <option value="1" {{$edit_buy_prebook->status=="1"?"selected":""}}>Active</option>
                                    <option value="0" {{$edit_buy_prebook->status=="0"?"selected":""}}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Start Date (No Editable)</label>
                                <input value="{{$edit_buy_prebook->created_at->format("Y-m-d")}}" class="form-control" readonly>

                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Expiration Date (Editable)</label>
                                <input value="{{DateTime::createFromFormat('Y-m-d', $edit_buy_prebook->activation_date)->add(new DateInterval('P1Y'))->format("Y-m-d")}}" class="form-control" data-toggle="datepicker" name="activation_date" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin_actions">
                    <button class="btn btn-primary" id="delete_button">{{ __('Save') }}</button>
                    <a class="btn btn-default" href="{{route("admin_prebooks")}}">Cancel</a>
                </div>
            </form>

            <form id="cancel_class" action="{{route('cancel_prebook')}}" method="post">
                <input type="hidden" id="classtocancel" name="prebook" value="0">
                {{ csrf_field() }}
            </form>
        </div>

    @endif

    <div id="modal-prebooks" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Student's Prebook</h4>
                </div>
                <div class="modal-body" style="max-width: 100%;">
                    @if(count($prebooks)>0)
                        <div class="classes-confirm" style="margin-left: 20px; margin-right: 20px;">
                            @foreach($prebooks as $prebook)
                                <div class="class-confirm">
                                    <img src="{{asset("assets/users/photos/".$prebook->teacher->id.".jpg?v=".rand())}}" alt="{{$prebook->teacher->first_name}}" />
                                    <div class="teacher_name">
                                        {{$prebook->teacher->first_name}} <span class="teacher_email">Zoom: {{$prebook->teacher->zoom_email}}</span>
                                    </div>
                                    <div class="teacher_time">
                                        {{DateTime::createFromFormat("U",strtotime('monday this week'))->add(new DateInterval("P".($prebook->day-1)."D"))->format("l")}} at {{DateTime::createFromFormat("H:i:s",$prebook->hour)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                                    </div>
                                    <div class="cancel-class prebook-{{$prebook->id}}" id="cancel-prebook">
                                        <form id="cancel_class_{{$prebook->id}}" action="{{route('admin_cancel_prebooks')}}" method="post">
                                            {{ csrf_field() }}
                                            <input type="hidden" id="classtocancel" name="prebook" value="{{$prebook->id}}">
                                            <input type="hidden" id="buy_prebook_id" name="buy_prebook_id" value="{{$edit_buy_prebook->id}}">
                                            Cancel
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">Without prebooks!</p>
                    @endif
                </div>
                <div class="modal-footer">

                </div>
            </div>
        </div>
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            @foreach($prebooks as $prebook)
                $(".prebook-{{$prebook->id}}").click(function () {
                    $("#cancel_class_{{$prebook->id}}").submit();
                });
            @endforeach
        });
    </script>
@endsection