@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="calendar">

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


        <div class="calendar-container">
            <div class="calendar-container-title">
                {{ __('Class History') }}
            </div>
            <div class="calendar-container-desc">
                <p>{{ __('This is a complete list of all the classes you have taken in the past') }}. For statistics about your usage, see your <a href="{{route("profile_progress")}}">{{ __('My Progress') }}</a> page.</p>
            </div>
        </div>

        <div class="history-container">

        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            var count_skip=0;

            function loadHistory(skip,page) {
                $.get("{{route("history_classes")}}/"+skip+"/"+page, function(data) {
                   $(".history-container").append(data);
                });

                if(count_skip==0){
                    count_skip+=10;
                }
            }

            $("body").delegate(".load-more-history","click",function () {
                loadHistory(count_skip,1);
                count_skip+=10;
                $(this).remove();
            });

            loadHistory(count_skip,1);

        })
    </script>
@endsection