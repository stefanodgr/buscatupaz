@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_users")}}">
                    User <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Edit <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_users_trash",["user_id"=>$edit_user->id])}}" class="btn btn-default">{{ __('Delete') }}</a>
                <a href="{{route("admin_users")}}" class="btn btn-default">Cancel</a>
            </div>
        </div>

        <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="edit_action">

            @if($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="bs-callout bs-callout-danger">
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

            <h1>Edit User</h1>

            <form action="{{route("admin_users_update")}}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{$edit_user->id}}"/>

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('First Name') }}</label>
                                <input class="form-control" value="{{$edit_user->first_name}}" placeholder="{{ __('First Name') }}" name="first_name" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('Last Name') }}</label>
                                <input class="form-control" value="{{$edit_user->last_name}}" placeholder="{{ __('Last Name') }}" name="last_name" required/>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>ID Number</label>
                                <input class="form-control" value="{{$edit_user->id_number}}" placeholder="ID Number" name="id_number" />
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Mobile Number</label>
                                <input class="form-control" value="{{$edit_user->mobile_number}}" placeholder="Mobile Number" name="mobile_number" />
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Email</label>
                                <input type="email" class="form-control" value="{{$edit_user->email}}" placeholder="Email" name="email" />
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Zoom Email</label>
                                <input type="email" class="form-control" value="{{$edit_user->zoom_email}}" placeholder="Zoom Email" name="zoom_email" />
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('Timezone') }}</label>
                                <select class="form-control" name="timezone">
                                    <option value="UTC">-</option>
                                    @foreach($edit_user->getTimeZones() as $zone_title=>$zone)
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
                                    <input type="checkbox" class="checkbox-switch" {{$edit_user->activated?"checked":""}} name="activated">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Chargebee ID</label>
                                <input class="form-control" name="chargebee_id" value="{{$edit_user->chargebee_id}}">
                            </div>

                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Roles</label>
                                <select class="form-control" name="roles[]" multiple required>
                                    @foreach($roles as $rol)
                                        <option value="{{$rol->name}}" {{$edit_user->hasRole($rol->name)?"selected":""}}>{{$rol->display_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('change Password') }}</label>
                                <input type="password" class="form-control" name="password"/>
                            </div>

                            <div class="col-xs-12 col-sm-6">
                                <label>Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password"/>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>change Profile Picture</label>
                                <input name="change_profile_picture" type="file"/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Current Profile Picture</label>
                                @if(file_exists("assets/users/photos/".$edit_user->id.".jpg"))
                                    <div>
                                        <img class="img-teacher-inm" src="{{asset("assets/users/photos/".$edit_user->id.".jpg?v=".rand())}}" alt="{{$edit_user->first_name}}"/>
                                    </div>
                                @else
                                    <div>
                                        <img class="img-teacher-inm" src="{{asset('img/user.png')}}" alt="No User Image">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($edit_user->hasRole("student"))
                    <div class="admin-section">
                        <div class="admin-section-desc">
                            <p>Student Options</p>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>Favorite Teacher</label>
                                    <select class="form-control" name="favorite_teacher">
                                        @foreach($teachers as $teacher)
                                            @if(!$edit_user->favorite_teacher)
                                                <option>Seleccione</option>
                                            @endif
                                            <option value="{{$teacher->id}}" {{$edit_user->favorite_teacher==$teacher->id?"selected":""}}>{{$teacher->first_name}} {{$teacher->last_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>Credits</label>
                                    <input type="number" class="form-control" value="{{$edit_user->credits}}" min="0" placeholder="Credits" name="credits" />
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>Former Progress Sheet</label>
                                    <input class="form-control" name="real_sheet" value="{{$edit_user->real_sheet}}">
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>Progress File DELE</label>
                                    <input class="form-control" name="dele_sheet" value="{{$edit_user->dele_sheet}}">
                                </div>

                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>Progress File RW</label>
                                    <input class="form-control" name="electives_sheet" value="{{$edit_user->electives_sheet}}">
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>User Level</label>
                                    <input class="form-control" name="user_level" value="{{$edit_user->user_level}}">
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                @if($student_location)
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Student Location</label>
                                        <input class="form-control" value="{{ucwords(strtolower($student_location))}}" disabled>
                                    </div>
                                @elseif($student_inmersion)
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Student Location</label>
                                        <select name="location_id" class="form-control" required>
                                                <option value="none" {{$edit_user->location==null?"selected":""}}>None</option>
                                            @foreach($locations as $location)
                                                <option value="{{$location->id}}" {{$edit_user->location_id==$location->id?"selected":""}}>{{ucwords(strtolower($location->name))}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Student Location</label>
                                        <input class="form-control" value="Online" disabled>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    @if($edit_user->getCurrentSubscription())

                                        <label>Subscription</label>
                                        <select class="form-control" name="subscription_plan">
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["baselang_149","baselang_149_trial"])?"selected":""}} value="baselang_149">Buscatupaz 149</option>
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["baselang_129","baselang_129_trial"])?"selected":""}} value="baselang_129">Buscatupaz 129</option>
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["baselang_99","baselang_99_trial"])?"selected":""}} value="baselang_99">Buscatupaz 99</option>
                                            <option {{$edit_user->getCurrentSubscription()->plan->name=="baselang_hourly"?"selected":""}} value="baselang_hourly">Buscatupaz Hourly</option>
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["baselang_dele","baselang_dele_trial","baselang_dele_test"])?"selected":""}} value="baselang_dele">Buscatupaz DELE</option>
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["baselang_dele_realworld","baselang_dele_realworld_trial"])?"selected":""}} value="baselang_dele_realworld">Buscatupaz DELE+RW</option>
                                            <option {{$edit_user->getCurrentSubscription()->plan->name=="9zhg"?"selected":""}} value="9zhg">9zhg (Year)</option>
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["medellin_RW","medellin_RW_trial"])?"selected":""}} value="medellin_RW">Medellin RW</option>
                                            <option {{in_array($edit_user->getCurrentSubscription()->plan->name,["medellin_DELE","medellin_DELE_trial"])?"selected":""}} value="medellin_DELE">Medellin DELE</option>
                                       	    <option {{$edit_user->getCurrentSubscription()->plan->name=="medellin_RW_1199"?"selected":""}} value="medellin_RW_1199 ">Medellin RW 1199 </option>
                                            <option {{$edit_user->getCurrentSubscription()->plan->name=="medellin_RW_Lite"?"selected":""}} value="medellin_RW_Lite ">Medellin RW Lite </option>
					 </select>

                                    @else
                                        <div class="centered">
                                            <label>No subscription available</label>
                                        </div>
                                    @endif
                                    <p>
                                        <a class="btn btn-primary" href="{{route("admin_users_update_subscription",["user_id"=>$edit_user->id])}}">Refresh from Chargebee</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6 centered">
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#cancelmodal">Cancel Subscription</button>
                                </div>
                                <!--div class="col-xs-12 col-sm-6 centered">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#freedaysmodal">Add Free Days</button>
                                </div-->
                            </div>
                        </div>

                        @if(count($edit_user->freedays)>0)
                            <br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <th>Free Days</th>
                                        <th>Register Date</th>
                                        <th>User</th>
                                    </thead>
                                    <tbody>
                                        @foreach($edit_user->freedays as $freedays)
                                            <tr>
                                                <td>{{$freedays->free_days}}</td>
                                                <td>{{$freedays->created_at==null?"N/A":$freedays->created_at->format("Y/m/d")}}</td>
                                                <td>{{$freedays->referred==null?"N/A":$freedays->referred->email}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="electives-container">
                            <div class="electives-header">
                                Electives
                            </div>

                            <div>
                                @if($edit_user->getElectives()->count()>0)
                                    @foreach($edit_user->getElectives() as $elective)
                                        <div class="elective-title">
                                            {{$elective->name}}
                                        </div>
                                    @endforeach
                                @else
                                    <div class="no-electives">
                                        User doesn't have electives
                                    </div>
                                @endif
                            </div>
                            @if($edit_user->getElectivesLeft()->count()>0)
                                <div class="elective-actions">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#electives_modal">Add Elective</button>
                                </div>
                            @endif

                        </div>

                        @if(!$edit_user->dele_trial_test && (!$current_subscription || ($current_subscription && ($current_subscription->plan=="baselang_hourly" || $current_subscription->plan=="baselang_129" || $current_subscription->plan=="baselang_149"))))
                            <div class="electives-container">
                                <div class="electives-header">
                                    DELE Trial
                                </div>
                                <br>
                                <div class="elective-actions">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#dele_trial_modal">Add DELE Trial</button>
                                </div>
                            </div>
                        @endif

                        @if($edit_user->buy_prebooks->where("status",1)->first())
                            <br>
                            <div class="admin-section-desc">
                                <p>Update Prebook</p>
                            </div>
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Type</label>
                                        <select class="form-control" name="type">
                                            <option value="silver" {{$edit_user->buy_prebooks->where("status",1)->first()->type=="silver"?"selected":""}}>Silver</option>
                                            <option value="gold" {{$edit_user->buy_prebooks->where("status",1)->first()->type=="gold"?"selected":""}}>Gold</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Status</label>
                                        <select class="form-control" name="status">
                                            <option value="1" {{$edit_user->buy_prebooks->where("status",1)->first()->status=="1"?"selected":""}}>Active</option>
                                            <option value="0" {{$edit_user->buy_prebooks->where("status",1)->first()->status=="0"?"selected":""}}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Start Date (No Editable)</label>
                                        <input value="{{$edit_user->buy_prebooks->where("status",1)->first()->created_at->format("Y-m-d")}}" class="form-control" readonly>

                                    </div>
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Expiration Date (Editable)</label>
                                        <input value="{{DateTime::createFromFormat('Y-m-d', $edit_user->buy_prebooks->where("status",1)->first()->activation_date)->add(new DateInterval('P1Y'))->format("Y-m-d")}}" class="form-control" data-toggle="datepicker" name="activation_date" readonly>
                                    </div>
                                </div>
                            </div>
                        @else
                            <br>
                            <div class="admin-section-desc">
                                <p>Add Prebook</p>
                            </div>
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Type</label>
                                        <select class="form-control" name="type">
                                            <option value="none">None</option>
                                            <option value="silver">Silver</option>
                                            <option value="gold">Gold</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-12 col-sm-6">
                                        <label>Expiration Date</label>
                                        <input class="form-control" placeholder="Select the expiration date" data-toggle="datepicker" name="activation_date" readonly>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif

                @if($edit_user->hasRole("teacher"))
                    <div class="admin-section">
                        <div class="admin-section-desc">
                            <p>Teacher Options</p>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>Zoom ID</label>
                                    <input type="text" class="form-control" value="{{$edit_user->zoom_id}}" placeholder="ID de Zoom" name="zoom_id" />
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>Location</label>
                                    <input type="text" class="form-control" value="{{$edit_user->location}}" placeholder="Location" name="location" />
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>Gender</label>
                                    <select class="form-control" name="gender">
                                        <option value="Men" {{$edit_user->gender=="Men"?"selected":""}}>Men</option>
                                        <option value="Women" {{$edit_user->gender=="Women"?"selected":""}}>Women</option>
                                    </select>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>Teaching Style</label>
                                    <select class="form-control" name="teaching_style">
                                        <option value="Conversational" {{$edit_user->teaching_style=="Conversational"?"selected":""}}>Conversational</option>
                                        <option value="Detail & Grammar Focused" {{$edit_user->teaching_style=="Detail & Grammar Focused"?"selected":""}}>Detail & Grammar Focused</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <label>Strongest with</label>
                                    <select class="form-control" name="strongest_with">
                                        <option value="Beginners" {{$edit_user->strongest_with=="Beginners"?"selected":""}}>Beginners</option>
                                        <option value="Advanced Grammar / Students" {{$edit_user->strongest_with=="Advanced Grammar / Students"?"selected":""}}>Advanced Grammar / Students</option>
                                        <option value="Pronunciation" {{$edit_user->strongest_with=="Pronunciation"?"selected":""}}>Pronunciation</option>
                                    </select>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <label>English Level</label>
                                    <select class="form-control" name="english_level">
                                        <option value="None" {{$edit_user->english_level=="None"?"selected":""}}>None</option>
                                        <option value="Good" {{$edit_user->english_level=="Good"?"selected":""}}>Good</option>
                                        <option value="Great" {{$edit_user->english_level=="Great"?"selected":""}}>Great</option>
                                        <option value="Near-Native" {{$edit_user->english_level=="Near-Native"?"selected":""}}>Near-Native</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    <label>Youtube</label>
                                    <input type="text" class="form-control" value="{{$edit_user->youtube_url}}" placeholder="Youtube URL" name="youtube_url" />
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-4">
                                    <label>DELE</label>
                                    <div>
                                        <input type="checkbox" class="checkbox-switch" {{$edit_user->is_deleteacher?"checked":""}} name="is_deleteacher">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4">
                                    <label>Block Online</label>
                                    <div>
                                        <input type="checkbox" class="checkbox-switch" {{$edit_user->block_online?"checked":""}} name="block_online">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4">
                                    <label>Block Prebook</label>
                                    <div>
                                        <input type="checkbox" class="checkbox-switch" {{$edit_user->block_prebook?"checked":""}} name="block_prebook">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    <label>School Location</label>
                                    <select class="form-control" name="teacher_locations[]" multiple>
                                        @foreach($locations as $location)
                                            <option value="{{$location->id}}" {{$edit_user->hasLocation($location->id)?"selected":""}}>{{ucwords(strtolower($location->name))}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    <label>Interests</label>
                                    <select multiple name="interests[]" class="form-control select-interests">
                                        @foreach($interests as $interest)
                                            <option value="{{$interest->id}}" {{$edit_user->interests->contains($interest->id)?"selected":""}}>{{$interest->title}}</option>
                                        @endforeach
                                    </select>
                                    <div class="new-interests"></div>

                                    <button type="button" class="add-interest btn btn-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12">
                                    <table id="user_calendar">
                                        <thead>
                                            <tr>
                                                <th>Mo</th>
                                                <th>Tu</th>
                                                <th>We</th>
                                                <th>Th</th>
                                                <th>Fr</th>
                                                <th>Sa</th>
                                                <th>Su</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @for($i=1;$i<=7;$i++)
                                                    <td>
                                                        @foreach($edit_user->getSavedCalendar($i) as $calendar_item)
                                                            <div class="interval-container">
                                                                <input name="user_calendar[{{$i}}][from][]" value="{{DateTime::createFromFormat("H:i:s",$calendar_item->from)->setTimezone(new DateTimeZone($edit_user->timezone))->format("H:i")}}" class="form-control" />
                                                                <input name="user_calendar[{{$i}}][till][]" value="{{DateTime::createFromFormat("H:i:s",$calendar_item->till)->setTimezone(new DateTimeZone($edit_user->timezone))->format("H:i")}}" class="form-control" />
                                                                <button type="button" class="remove-interval btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                                            </div>
                                                        @endforeach

                                                        <button type="button" class="add-interval btn btn-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                                    </td>
                                                @endfor
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif

                @if(count($edit_user->log_admin)>0)
                    <div class="table-responsive">
                        <div class="admin-section">
                            <br>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <th>Admin Mail</th>
                                    <th>Field</th>
                                    <th>Old Data</th>
                                    <th>New Data</th>
                                    <th>Date of change</th>
                                </thead>
                                <tbody>
                                    @foreach($edit_user->log_admin->sortByDesc("created_at") as $log)
                                        <tr>
                                            <td>{{$log->admin_mail}}</td>
                                            <td>{{$log->field}}</td>
                                            <td>{{$log->old_data}}</td>
                                            <td>{{$log->new_data}}</td>
                                            <td>{{$log->created_at==null?"N/A":$log->created_at->format("Y/m/d")}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                     </div>
                @endif

                <div class="admin_actions">
                <button class="btn btn-primary" id="delete_button">{{ __('Save') }}</button>
                <a class="btn btn-default" href="{{route("admin_users")}}">Cancel</a>
                </div>
            </form>
            @if($edit_user->hasRole("student"))
                <div id="freedaysmodal" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <form action="{{route("admin_users_add_days")}}" method="post">
                                {{ csrf_field() }}
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Add Free Days</h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" value="{{$edit_user->id}}" min="0"/>
                                    <input type="number" class="form-control" placeholder="Days" name="days"/>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary">Add</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                @if($edit_user->getElectivesLeft()->count()>0)
                    <div id="electives_modal" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <form action="{{route("admin_users_add_elective")}}" method="post">
                                    {{ csrf_field() }}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Add Elective</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="{{$edit_user->id}}"/>
                                        <select name="elective" class="form-control">
                                            @foreach($edit_user->getElectivesLeft() as $elective)
                                                <option value="{{$elective->id}}">{{$elective->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary">Add</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                @endif

                @if(!$current_subscription || ($current_subscription && ($current_subscription->plan=="baselang_hourly" || $current_subscription->plan=="baselang_129" || $current_subscription->plan=="baselang_149")))
                    <div id="dele_trial_modal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <form action="{{route("admin_users_add_dele_trial")}}" method="post">
                                    {{ csrf_field() }}
                                    <div class="modal-header">
                                        <h4 class="modal-title">Add DELE Trial</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="{{$edit_user->id}}"/>
                                        @if(!$current_subscription)
                                            <input type="hidden" name="plan" value="null"/>
                                        @elseif($current_subscription && $current_subscription->plan=="baselang_hourly")
                                            <input type="hidden" name="plan" value="{{$current_subscription->plan}}"/>
                                        @elseif($current_subscription && $current_subscription->plan=="baselang_129")
                                            <input type="hidden" name="plan" value="{{$current_subscription->plan}}"/>
                                        @elseif($current_subscription && $current_subscription->plan=="baselang_149")
                                            <input type="hidden" name="plan" value="{{$current_subscription->plan}}"/>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Confirm</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>

    @endif

    <div id="cancelmodal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p></p>
                </div>
                <div class="modal-footer">
                    <div class="instant instant-option">
                        <a class="btn btn-primary btn-block" href="{{route("admin_users_cancel_subscription_immediately",["user_id"=>$edit_user->id])}}">Cancel immediately</a>
                    </div>
                    
                    <div class="instant instant-option">
                        <a class="btn btn-primary btn-block" href="{{route("admin_users_cancel_subscription",["user_id"=>$edit_user->id])}}">Cancel at end of subscription</a>
                    </div>
                    <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            $(".checkbox-switch").bootstrapSwitch();
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            $("body").delegate(".remove-interval","click",function () {
                $(this).parent().remove();
            });


            $("body").delegate(".add-interest","click",function () {
                $(".new-interests").append('<div class="interest_interval"><input name="new_interest[]" value="" class="form-control" /><button type="button" class="remove-interval btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button></div>');
            });


            $("body").delegate(".add-interval","click",function () {
                var day_interval=($(this).parent().prevAll().length+1);

                $(this).before('<div class="interval-container"><input name="user_calendar['+day_interval+'][from][]" value="" class="form-control" /><input name="user_calendar['+day_interval+'][till][]" value="" class="form-control" /><button type="button" class="remove-interval btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button></div>');


            });

        });
    </script>
@endsection
