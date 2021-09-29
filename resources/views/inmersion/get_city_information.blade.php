@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("city_information")}}">
                    City Information <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    {{$information_content->name}}
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="lessons">

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
            {{$information_content->name}}
        </div>

        <div class="lessons-description">
            {!!$information_content->description!!}
        </div>

        <div class="levels-list">
            @foreach($information_contents as $information)
                <div class="level lesson-item">
                    <a href="{{route("get_information_content",["info_slug"=>$information_content->slug, "info_slug_content"=>$information->slug])}}">
                        {{$information->name}}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection