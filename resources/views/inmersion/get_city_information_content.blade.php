@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb {{(isset($lesson->externalurl) && !empty($lesson->externalurl) && $lesson->externalurl)?"externalurl":""}}">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("city_information")}}">
                    City Information <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item" href="{{route("get_information",["info_slug"=>$information_content->slug])}}">
                    {{$information_content->name}} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    {{$content->name}} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}} lesson-main-content-wrapper" id="lessons">

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

        <div class="lesson-title">
            {{$content->name}}
        </div>

        <div class="lesson-description">
            {!!$content->description!!}
        </div>

    </div>
@endsection