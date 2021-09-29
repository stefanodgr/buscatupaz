@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_locations")}}">
                    Locations <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Edit <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_locations_trash",["location_id"=>$location->id])}}" class="btn btn-default">{{ __('Delete') }}</a>
                <a href="{{route("admin_locations")}}" class="btn btn-default">Cancel</a>
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

            <h1>Edit Location</h1>

            <form action="{{route("admin_locations_update")}}" method="post" >
                {{ csrf_field() }}
                <input type="hidden" name="location_id" value="{{$location->id}}"/>
                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Nombre</label>
                                <input class="form-control" value="{{ucwords(strtolower($location->name))}}" placeholder="Nombre" name="name" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>{{ __('Timezone') }}</label>
                                <select class="form-control" name="timezone">
                                    <option value="UTC">-</option>
                                    @foreach($user->getTimeZones() as $zone_title=>$zone)
                                        <optgroup label="{{$zone_title}}">
                                            @foreach($zone as $timeZone)
                                                <option value="{{$timeZone[0]}}" @if($timeZone[0]==$location->timezone) selected @endif>{{$timeZone[1]}} - {{$timeZone[2]}}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Time Message</label>
                                <textarea rows="3" class="form-control" placeholder="e.g. You can choose to take all four weeks in the morning (9:30am - 1:30pm) or afternoon (2:30pm-6:30pm) slot Eastern Time US." name="time_message" required>{{$location->time_message}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Email Message</label>
                                <textarea rows="4" class="form-control" placeholder="e.g. Your login information for the online platform, where you'll find advice videos and where you'll be able to book unlimited extra conversation practice with our online teachers during the above dates is:" name="email_message" required>{{$location->email_message}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Survey</label>
                                <input type="text" class="form-control" placeholder="e.g. https://baselang.typeform.com/to/ifGX3p" value="{{$location->survey}}" name="survey" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Price</label>
                                <input type="number" min="1" class="form-control" placeholder="e.g. 600$" value="{{$location->price}}" name="price" required/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin_actions">
                    <button class="btn btn-primary" id="delete_button">{{ __('Save') }}</button>
                    <a class="btn btn-default" href="{{route("admin_locations")}}">Cancel</a>
                </div>
            </form>

        </div>

    @endif
@endsection