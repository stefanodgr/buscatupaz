@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_levels")}}">
                    Free Days <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Add <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
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

            <h1>Add Free Days</h1>

            <form id="form-free-days" action="{{route("admin_add_free_days")}}" method="post" >
                {{ csrf_field() }}

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Subscription</label>
                                <select class="form-control" name="subscription" required>
                                    <option value="Hourly">Hourly</option>
                                    <option value="Real World">Real World</option>
                                    <option value="DELE">DELE</option>
                                    <option value="Medellin Real World">Medellin Real World</option>
                                    <option value="Medellin DELE">Medellin DELE</option>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Days</label>
                                <input type="number" class="form-control" placeholder="Days" id="days" name="days" min="1" required/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin_actions">
                    <button type="button" class="btn btn-primary" id="delete_button">{{ __('Save') }}</button>
                    <a class="btn btn-default" href="{{route("dashboard")}}">Cancel</a>
                </div>

            </form>

        </div>

        <div id="freedaysmodal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Confirm your password!</h4>
                    </div>
                    <div class="modal-body">
                        <input type="password" class="form-control" placeholder="Password" id="password" required/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="confirm_password" class="btn btn-primary">Confirm</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    @endif
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {

            $("#delete_button").click(function () {
                var days = $("#days").val();
                if(days=="") {
                    alert("Enter the number of days!");
                }else {
                    $('#freedaysmodal').modal('show');
                }
            });

            $("#confirm_password").click(function () {
                var password = $("#password").val();
                if(password=="") {
                    alert("Enter your password!");
                }else {
                    $('#freedaysmodal').modal('toggle');
                    $.post("{{route("admin_confirm_free_days")}}", {
                        "_token": "{{csrf_token()}}",
                        "password": password,
                    }, function (data) {
                        if(data.response=="success") {
                            $("#delete_button").prop('disabled', true);
                            $("#form-free-days").submit();
                        }else if(data.response=="error") {
                            alert("The password you entered is incorrect!");
                        }
                    });
                }
            });

        });
    </script>
@endsection