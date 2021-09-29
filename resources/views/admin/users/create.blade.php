@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_users")}}">
                    Users <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Create <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_users")}}" class="btn btn-default">Cancel</a>
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

            <h1>Create User</h1>



            <form action="{{route("admin_users_create")}}" method="post" id="form_create_user">
                {{ csrf_field() }}

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Name</label>
                                <input class="form-control" value="" placeholder="Name" name="first_name" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('Last Name') }}</label>
                                <input class="form-control" value="" placeholder="{{ __('Last Name') }}" name="last_name" required/>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>ID Number</label>
                                <input class="form-control" value="" placeholder="Numero de Identificación" name="id_number" />
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Mobile Number</label>
                                <input class="form-control" value="" placeholder="Mobile Number" name="mobile_number" />
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Email</label>
                                <input type="email" class="form-control" value="" placeholder="Email" name="email" />
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Zoom Email</label>
                                <input type="email" class="form-control" value="" placeholder="Zoom Email" name="zoom_email" />
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>TimeZone</label>
                                <select class="form-control" name="timezone">
                                    <option value="UTC">-</option>
                                    @foreach($user->getTimeZones() as $zone_title=>$zone)
                                        <optgroup label="{{$zone_title}}">
                                            @foreach($zone as $timeZone)
                                                <option value="{{$timeZone[0]}}" {{$timeZone[3]?"selected":""}}>{{$timeZone[1]}} - {{$timeZone[2]}}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Activate</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" checked name="activated">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Description</label>
                                <textarea class="form-control" placeholder="Description" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Roles</label>
                                <select class="form-control" name="roles[]" multiple>
                                    @foreach($roles as $rol)
                                        <option value="{{$rol->name}}">{{$rol->display_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="admin_actions">
                    <button class="btn btn-primary">Create</button>
                    <a class="btn btn-default" href="{{route("admin_users")}}">Cancel</a>
                </div>
            </form>
        </div>

    @endif
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            $(".checkbox-switch").bootstrapSwitch();
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });


        });
    </script>
@endsection