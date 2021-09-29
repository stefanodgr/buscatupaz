@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
            </div>
            <div class="breadcrumb-actions">
                <div class="breadcrumb-actions-wrapper">
                </div>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="teachers">
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

        <div id="teachers-filters">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="filter filter-or filter-gender">
                            <div class="filter-title">
                                NAME
                            </div>
                            <div class="filter-options">
                                <input placeholder="Name of teacher" type="text" class="form-control first-name-teacher">
                            </div>
                        </div>

                        <div class="filter filter-or filter-gender">
                            <div class="filter-title">
                                GENDER
                            </div>
                            <div class="filter-options">
                                <button type="button" class="btn btn-default">Men</button>
                                <button type="button" class="btn btn-default">Women</button>
                            </div>
                        </div>

                        <div class="filter filter-or filter-teaching-style">
                            <div class="filter-title">
                                TEACHING STYLE <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="Most teachers have a more conversational style, but some focus more on grammar and details. If you prefer a particular style, select it here. If you’re not sure, we recommend a conversational style unless you are struggling with a particular concept, or are in the advanced levels when the details start to matter."></i>
                            </div>
                            <div class="filter-options">
                                <button type="button" class="btn btn-default">Conversational</button>
                                <button type="button" class="btn btn-default">Detail & Grammar Focused</button>
                            </div>
                        </div>

                        <div class="filter filter-or filter-strongest-with">
                            <div class="filter-title">
                                STRONGEST WITH <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="While all teachers can handle every part of the language and all levels, each teacher will naturally be best with students at a particular stage."></i>
                            </div>
                            <div class="filter-options">
                                <button type="button" class="btn btn-default">Beginners</button>
                                <button type="button" class="btn btn-default">Advanced Grammar / Students</button>
                                <button type="button" class="btn btn-default">Pronunciation</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6">

                        <div class="filter filter-and filter-interests">
                            <div class="filter-title">
                                INTERESTS <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="With conversations core to how classes work at BaseLang, it’s important to become friends with your teachers. Having similar interests makes having long conversations with them much easier, and increases the chances that you’ll “click” with a teacher. Note: if you select three interests, for example, only teachers who have all three interests will show up. If you get too few teachers in the results, remove some filters!"></i>
                            </div>
                            <div class="filter-options">

                                @foreach($interests as $interest)
                                    <button type="button" class="btn btn-default">{{$interest->title}}</button>
                                @endforeach

                            </div>
                        </div>

                        <div class="filter filter-or filter-english-level">
                            <div class="filter-title">
                                LEVEL OF ENGLISH <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="We try to only hire teachers with great or near-native English, but we have some with just good English as well. They have stayed around because they’ve become many student’s favorites, and it doesn’t matter as much at an advanced level. Most teachers have “great” English.<br />We have a handful of teachers who speak no English at all as well, specifically for advanced students. These teachers are great at advanced grammar."></i>
                            </div>
                            <div class="filter-options filter-english-level-options">
                                <button type="button" class="btn btn-default">None</button>
                                <button type="button" class="btn btn-default">Good</button>
                                <button type="button" class="btn btn-default">Great</button>
                                <button type="button" class="btn btn-default">Near-Native</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="container-fluid filter-actions">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <button class="btn btn-primary filter-action">Show Teachers</button>
                        <button class="btn btn-default clear-action">Clear Filters</button>
                    </div>
                    <div class="col-sm-12 col-md-6 inquireaction-container">
                        <button class="btn btn-default inquireaction" data-toggle="modal" data-target="#inquire-abilities">Inquire About Specialized Abilities</button>

                        <div id="inquire-abilities" class="modal fade" role="dialog">
                            <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Specialized Abilities</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>A small handful of our teachers have specialized abilities that go beyond what we typically offer.</p>

                                        <p><a href="https://baselang.com/support/list-teachers-can-teach-children/" target="_blank">Click here</a> for the list of teachers who can teach children.</p>

                                        <p><a href="https://baselang.com/support/list-teachers-can-teach-children/" target="_blank">Click here</a> for the list of teachers who can teach Spain-specific grammar.</p>
                                    </div>
                                    <div class="modal-footer">
                                    </div>
                                </div>

                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <div id="teachers-container">
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            function loadTeachers(){
                var filters={"_token":"{{csrf_token()}}"};
                filters.gender=$(".filter-gender .active").text();
                filters.teaching_style=$(".filter-teaching-style .active").text();
                filters.strongest_with=$(".filter-strongest-with .active").text();
                filters.english_level=$(".filter-english-level .active").text();
                filters.filter_interests=[];

                $.each($(".filter-interests .active"),function (k,v) {
                    filters.filter_interests.push($(v).text());
                });

                filters.first_name=$(".first-name-teacher").val();

                @if(Route::currentRouteName()=="teachers")
                    $("#teachers-container").load("{{route("get_teachers")}}",filters,function(){

                    });
                @else
                    $("#teachers-container").load("{{route("get_teachers_school")}}",filters,function(){

                    });
                @endif
            }

            $(".filter-action").click(function(){
                loadTeachers();
            });

            $(".clear-action").click(function(){
                $(".filter button").removeClass("active");
                $(".first-name-teacher").val("");
            });

            $(".filter button").click(function(){
                if($(this).hasClass("active")){
                    $(this).removeClass("active");
                    return true;
                }

                if ($(this).parents('.filter-or').length) {
                    $(this).siblings("button").removeClass("active");
                }

                $(this).addClass("active");

            });

            $("body").delegate(".teacher-video-popup,.teacher-video-popup-responsive","click",function () {
                $('#video-teacher .teacher-video').attr("src","https://www.youtube.com/embed/"+$(this).attr("youtube-id")+"?showinfo=0&enablejsapi=1&autoplay=1&rel=0");
                $('#video-teacher').modal('show');
            });

            $("body").delegate(".set-favorite","click",function () {
                $('#favorite-teacher .teacher-name').text($(this).attr("teacher-name"));
                $('#favorite-teacher .teacher-id').val($(this).attr("teacher-id"));
                $('#favorite-teacher').modal('show');
            });

            $("body").delegate(".rating-starts i","click",function () {
                var startElement=$(this);
                $.post( "{{route("evaluate_teachers")}}",{"_token":"{{csrf_token()}}","evaluation":($(this).index())+1,"teacher_id":$(this).attr("teacher-id")}, function( data ) {

                    startElement.siblings().addBack().removeClass("fa-star");
                    startElement.siblings().addBack().removeClass("fa-star-o");
                    startElement.prevAll().addBack().addClass("fa-star");
                    startElement.nextAll().addClass("fa-star-o");
                });
            });

            $("body").delegate(".rating-starts i","mouseleave",function () {
                $(".rating-starts i").removeClass("hovered");
            });

            $("body").delegate(".rating-starts i","mouseenter",function () {
                //$(this).addClass("hovered");
                $(this).prevAll().addBack().addClass("hovered");
            });

            loadTeachers();

        })
    </script>
@endsection