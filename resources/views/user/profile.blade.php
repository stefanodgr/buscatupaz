@extends("layouts.main")

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
                {{ __('Basic Info') }}
            </div>
            <div class="profile-container-desc">
                <p>
                    {{ __('Set your basic info, like name, email, photo, and timezone here') }}.
                </p>
            </div>
        </div>

        <div class="basic-info-container">
            <form method="post" action="{{route("save_profile")}}" class="basic-info-form" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="first_name">{{ __('First Name') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="text" name="first_name" id="first_name" value="{{$user->first_name}}" required/>
                        </div>
                    </div>
                </div>
                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="last_name">{{ __('Last Name') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="text" name="last_name" id="last_name" value="{{$user->last_name}}" required/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="email">Email</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="email" name="email" id="email" value="{{$user->email}}" required/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="zoom_email">Zoom Email <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ __('Add_Zoom_Account_Description') }}."></i></label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="email" name="zoom_email" id="zoom_email" value="{{$user->zoom_email}}" required/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="password">{{ __('Password') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <input class="form-control" type="password" name="password" id="password" value="" placeholder="Enter new password to change"/>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="gcalendar">{{ __('Timezone') }} @if($user->location_id == 1) <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="You cannot change your timezone, as you are taking classes in-person and we don't want a timezone change to cause confusion. Everything is shown in the local time where your school is."></i> @endif </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <select class="form-control" name="timezone" @if($user->location_id == 1) disabled @endif>
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

                @if($user->getCurrentRol()->name=="teacher")


                    <div class="container-fluid field-container">
                        <div class="row">
                            <div class="col-xs-12 col-sm-4">
                                <label for="company">{{ __('Company') }}  <span class="small">({{ __('Optional') }})</span> <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ __('Add_Company_Description') }}."></i></label>
                            </div>
                            <div class="col-xs-12 col-sm-8">
                                <input class="form-control" type="text" name="company" id="company" value="{{$user->company}}" />
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid field-container">
                        <div class="row">
                            <div class="col-xs-12 col-sm-4">
                                <label for="youtube_url">Youtube</label>
                            </div>
                            <div class="col-xs-12 col-sm-8">
                                <input class="form-control" type="text" name="youtube_url" id="youtube_url" value="{{$user->youtube_url}}" />
                            </div>
                        </div>
                    </div>


                    <div class="container-fluid field-container">
                        <div class="row">
                            <div class="col-xs-12 col-sm-4">
                                <label for="user_calendar">{{__('User Calendar')}}</label>
                            </div>






                            <div class="col-xs-12 col-sm-12">

                                <div class="row">

                                    @for($i=1;$i<=7;$i++)
                                        <div class="col-xs-6 col-sm-6 weekday{{$i}}">
                                            @php

                                            switch($i) {

                                                case 1:
                                                    $day = new \Carbon\Carbon('next monday');
                                                    break;

                                                case 2:
                                                    $day = new \Carbon\Carbon('next tuesday');
                                                    break;

                                                case 3:
                                                    $day = new \Carbon\Carbon('next wednesday');
                                                    break;

                                                case 4:
                                                    $day = new \Carbon\Carbon('next thursday');
                                                    break;

                                                case 5:
                                                    $day = new \Carbon\Carbon('next friday');
                                                    break;

                                                case 6:
                                                    $day = new \Carbon\Carbon('next saturday');
                                                    break;

                                                case 7:
                                                    $day = new \Carbon\Carbon('next sunday');
                                                    break;

                                                default:
                                                break;

                                            }

                                            $today = new \Carbon\Carbon('now');
                                            $today->setTimeZone($user->timezone);
                                            $tomorrow = new \Carbon\Carbon('tomorrow');
                                            $tomorrow->setTimeZone($user->timezone);
                                            if($today->format('l')==$day->format('l')) {
                                                $day=$today;
                                                $daytitle=__("Today");
                                            } elseif($tomorrow->format('l')==$day->format('l')) {
                                                $day=$tomorrow;
                                                $daytitle=__("Tomorrow");
                                            } else {
                                                $daytitle=$day->toFormattedDateString();
                                            }


                                            @endphp
                                            <h4>{{$daytitle}}</h4>
                                            @foreach($user->getSavedCalendar($i) as $calendar_item)
                                                <div class="interval-container">
                                                    <input name="user_calendar[{{$i}}][from][]" value="{{DateTime::createFromFormat("H:i:s",$calendar_item->from)->setTimezone(new DateTimeZone($user->timezone))->format("H:i")}}" class="form-control timepickerstart" placeholder="{{__('Start')}}" />
                                                    <input name="user_calendar[{{$i}}][till][]" value="{{DateTime::createFromFormat("H:i:s",$calendar_item->till)->setTimezone(new DateTimeZone($user->timezone))->format("H:i")}}" class="form-control timepickerend"  placeholder="{{__('End')}}" />
                                                    <button type="button" class="remove-interval btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                </div>
                                            @endforeach

                                            <button type="button" class="add-interval btn btn-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                        </div>
                                    @endfor

                                </div>


                                <table id="user_calendar">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>

                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



                @endif

                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="description">{{ __('About me') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <textarea class="form-control" name="description" id="description" required>{!! $user->description !!}</textarea>
                        </div>
                    </div>
                </div>

                <div class="container-fluid field-container">
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="photo">{{ __('Photo') }}</label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <div class="photo-container">
                                <div class="photo-form">
                                    @if(file_exists("assets/users/photos/".$user->id.".jpg"))
                                        <img src="{{ asset('assets/users/photos/'.$user->id.'.jpg?v='.rand()) }}" alt="No User Image">
                                    @else
                                        <img src="{{ asset('img/user.png') }}" alt="No User Image">
                                    @endif

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
                    <div calss="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="gcalendar">{{ __('Google Calendar') }} <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{ __('Google Calendar') }} Integration<br /><br />Securely connect your Google account to automatically sync new classes to your {{ __('Google Calendar') }}. We don’t see any of your information, just add new events to your calendar."></i></label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            @if($google_email)
                                <input class="form-control" type="text" name="gcalendar" id="gcalendar" value="{{$google_email}}" disabled/>
                                <a href="{{route("unlink_google_account")}}" class="btn btn-primary btn-block gcalendar-disconnect">{{ __('Disconnect Google Account') }}</a>
                            @else
                                <a href="{{route("google_account")}}" class="gcalendar-connect">
                                    <img src="{{ asset('img/gcalendar.png') }}" alt="Connect Google Account icon">{{ __('Connect Google Account') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>

            </form>
        </div>

    </div>

@endsection

@section("scripts")

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.10.0/jquery.timepicker.min.js"></script>


    <script>
        $(document).ready(function () {

            $('.timepickerstart').timepicker({'forceRoundTime': true,'timeFormat': 'H:i'});
            $('.timepickerend').timepicker({
                'forceRoundTime': true,
                'minTime': '00:30',
                'showDuration': true,
                'timeFormat': 'H:i'

            });


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


            $("body").delegate(".add-interval","click",function () {
                var day_interval=($(this).parent().prevAll().length+1);

                $(this).before('<div class="interval-container"><input name="user_calendar['+day_interval+'][from][]" value="" placeholder="{{__('Start')}}" class="form-control timepickerstart" /><input placeholder="{{__('End')}}" name="user_calendar['+day_interval+'][till][]" value="" class="form-control timepickerend" /><button type="button" class="remove-interval btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button></div>');



                var timepickerstart = $(this).parent().find('.timepickerstart').last();

                timepickerstart.timepicker({
                    'scrollDefault': 'now',
                    'forceRoundTime': true,
                    'timeFormat': 'H:i'
                    }
                );


                var timepickerend = $(timepickerstart).parent().find('.timepickerend').last();
                timepickerend.timepicker({
                    'forceRoundTime': true,
                    'minTime': '00:30',
                    'showDuration': true,
                    'timeFormat': 'H:i'

                });



                timepickerstart.on('changeTime',function() {

                    var dtend = timepickerstart.timepicker('getTime');
                    dtend.setHours( dtend.getHours() + 1 );

                    timepickerend.timepicker('option',{'minTime':timepickerstart.timepicker('getTime')});
                    timepickerend.timepicker('setTime',dtend);

                });


            });

            $("body").delegate(".remove-interval","click",function () {
                $(this).parent().remove();
            });

        })
    </script>
@endsection