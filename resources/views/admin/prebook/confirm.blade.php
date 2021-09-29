@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item">
                    Select Teacher <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Confirm Prebook
                </a>
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
                Confirm Preebok
            </div>
            <div class="calendar-container-desc">
                {{ __('Please double check everything looks good and then click the confirmation button to finish booking') }}.
            </div>

            <div class="classes-confirm">
                @foreach($prebooks as $prebook)
                    @if($prebook[0])
                        <div class="class-confirm" class-info="{{$prebook[0]}},{{$prebook[1]}},{{$prebook[2]->id}}">
                            <img src="{{asset("assets/users/photos/".$prebook[2]->id.".jpg?v=".rand())}}" alt="{{$prebook[2]->first_name}}" />
                            <div class="teacher_name">
                                {{$prebook[2]->first_name}}
                            </div>
                            <div class="teacher_time">
                            	@if($prebook[0]==1) Mondays @elseif($prebook[0]==2) Tuesdays @elseif($prebook[0]==3) Wednesdays @elseif($prebook[0]==4) Thursdays @elseif($prebook[0]==5) Fridays @elseif($prebook[0]==6) Saturdays @elseif($prebook[0]==7) Sundays @endif at {{\DateTime::createFromFormat("h:iA",$prebook[1])->format("h:ia")}}
                            </div>
                            <div class="cancel-class">
                                {{ __('Delete') }}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

        </div>

        <div id="book-actions" class="confirm-action">
            <form id="post-calendar" action="{{route("admin_save_prebook")}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{$user_id}}">
            </form>
            <button type="button" class="submitcalendar btn btn-primary">Confirm Prebook</button>
            <button type="button" class="chooseteachers btn btn-default">{{ __('Back') }}</button>

        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            $(".submitcalendar").click(function () {
                $.each($( ".class-confirm" ),function(k,v){
                    $("#post-calendar").append('<input type="hidden" value="'+$(v).attr("class-info")+'" name="selected[]"/>');
                })
                $("#post-calendar").submit();
            });

            $(".cancel-class").click(function () {
                $(this).parent().remove();

                if($(".cancel-class").length==0){
                    window.location.replace("{{route("prebook_new")}}");
                }
            });

            $(".chooseteachers").click(function () {
                window.history.back()
            });

        })
    </script>
@endsection