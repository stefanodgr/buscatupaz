@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_block_day")}}">
                    Blocked Days <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Create <i class="fa fa-angle-right" aria-hidden="true"></i>
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

            <h1>Block Day</h1>

            <form action="{{route("admin_block_day_create")}}" method="post" >
                {{ csrf_field() }}
                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('Teacher') }}</label>
                                <select name="teacher_id" class="form-control" required>
                                    <option value="all">All teachers</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{$teacher->id}}">{{$teacher->first_name}} {{$teacher->last_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-3">
                                <label>Block Day</label>
                                <input name="blocking_day" id="date" class="form-control" data-toggle="datepicker" placeholder="Select a Day" readonly required>
                            </div>
                            <div class="col-xs-12 col-sm-3">
                                <label>Specify Hours</label>
                                <select id="specify_hours" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="input_hours"></div>

                </div>
                <div class="admin_actions">
                    <a class="btn btn-primary" id="delete_button">{{ __('Save') }}</a>
                    <a class="btn btn-default" href="{{route("admin_block_day")}}">Cancel</a>
                </div>

                <!--confirm model popup---->
                <div id="add-block-day" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"></h4>
                            </div>
                            <div class="modal-body">
                                    <div class="link-diff">
                                    Are you sure you want to block the day?
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <div class="instant instant-option">
                                        <button id="confirm_button" class="btn btn-primary btn-block">Confirm</button>
                                </div>
                                <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--confirm model popup----->
            </form>
        </div>
    @endif
@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            var todayDate = new Date();
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd',
                startDate: todayDate
            });

            $('#date').change(function () {
                $('#date').datepicker('hide');
            });

            $('#specify_hours').change(function () {

                var input_hours = $("#input_hours");
                input_hours.empty();

                if($(this).val()==1) {
                    input_hours.append(`
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>From (UTC)</label>
                                    <input class="form-control" placeholder="e.g. 14:00" name="from" required/>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>Till (UTC)</label>
                                    <input class="form-control" placeholder="e.g. 15:00" name="till" required/>
                                </div>
                            </div>
                        </div>`
                    );
                }

            });

            $("#delete_button").click(function(){
                 $("#add-block-day").modal("show");
            });

        })
    </script>
@endsection