@extends("layouts.main")

@section("content")
    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrump":""}}" id="lessons">
        
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

        <div class="lessons-title">
        	City Information {{$location?ucfirst($location->name):''}}
        </div>

        <div class="lessons-description">
        	Here you can find information about different aspects of the city.
        </div>

        <div class="levels-list">
            @foreach($information_contents as $information)
                <div class="level">
                    <a href="{{route("get_information",["info_slug"=>$information->slug])}}">
                    	{{$information->name}}
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection