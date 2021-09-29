@extends("layouts/main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="profile">

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


        <div class="profile-container">
            <div class="profile-container-title">
                {{__('Sign up as User')}}
            </div>
            <div class="profile-container-desc">
                <p>
                    {{ __('Set your basic info, like name, email, photo, and timezone here') }}.
                </p>
            </div>
        </div>

        <div class="basic-info-container">
            <form method="post" action="{{route("save_usersignup")}}" class="basic-info-form" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="create_provider" value="0">
                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="first_name">{{ __('First Name') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="text" name="first_name" id="first_name" value="{{old('first_name')}}" required/>
                        </div>
                    </div>
                </div>
                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="last_name">{{ __('Last Name') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="text" name="last_name" id="last_name" value="{{old('last_name')}}" required/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="email">Email</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="email" name="email" id="email" value="{{old('email')}}" required/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="zoom_email">Zoom Email <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ __('Add_Zoom_Account_Description') }}."></i>

                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <span class="small">Si no tienes cuenta de Zoom, ingresa de nuevo tu correo</span><br/>
                            <input class="form-control" type="email" name="zoom_email" id="zoom_email" value="{{old('zoom_email')}}" required/>

                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="password">{{ __('Password') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="password" name="password" id="password" value="" placeholder=""/>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="password_confirmation">{{ __('Password Confirmation') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" value="" placeholder=""/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div class="row">

                        <div class="col-xs-12 col-sm-4">
                            <label for="gcalendar">{{ __('Timezone') }}</label>
                        </div>

                        <div class="col-xs-12 col-sm-8">
                            <select class="form-control" name="timezone">
                                <option value="UTC">-</option>
                                @foreach($user->getTimeZones() as $k=>$zone)

                                    <optgroup label="{{$k}}">
                                        @foreach($zone as $timeZone)
                                            <option value="{{$timeZone[0]}}" {{$timeZone[3]?"selected":""}}>{{$timeZone[1]}} - {{$timeZone[2]}}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>



                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="description">{{ __('About me') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <textarea class="form-control" name="description" id="description" required>{{old('description')}}</textarea>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="photo">{{ __('Photo') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <div class="photo-container">
                                <div class="photo-form">


                                    <label for="photo-profile" class="btn btn-default">{{ __('Upload New') }}</label>
                                    @if(file_exists("assets/users/photos/".$user->id.".jpg"))
                                        <button type="button" class="btn btn-default btn-delete-photo">{{ __('Delete') }}</button>
                                    @endif
                                    <input type="hidden" name="delete-photo" value="0" class="delete-photo" id="delete-photo"/>
                                    <input type="file" name="photo" class="photo-profile" id="photo-profile" accept="image/*"/>



                                </div>
                                <div class="photo-help">
                                    <i class="save-for-upload">Click on save to confirm the changes.</i>
                                    <i>{{ __('For best results, upload a square photo 75px by 75px or larger. Max file size is 2MB') }}.</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <!-- <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="gcalendar">{{ __('Google Calendar') }} <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ __('Google Calendar') }} Integration<br /><br />Securely connect your Google account to automatically sync new classes to your {{ __('Google Calendar') }}. We don’t see any of your information, just add new events to your calendar."></i></label>
                        </div>

                    </div>
                    -->
                </div>

                <div class="profile-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>

            </form>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
            function readURL(input) {

                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('.photo-form img').attr('src', e.target.result);
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#photo-profile").change(function() {
                readURL(this);
                $(".save-for-upload").addClass("active");
                $("#delete-photo").val(0);
            });

            $(".btn-delete-photo").click(function () {
                $('.photo-form img').attr('src', "{{ asset('img/user.png') }}");
                $(".save-for-upload").addClass("active");
                $("#delete-photo").val(1);
            })
        })
    </script>
@endsection