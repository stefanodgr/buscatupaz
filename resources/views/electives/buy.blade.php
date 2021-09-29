@extends("layouts.fullscreen")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <div class="breadcrumb-actions-left">
                    <a href="{{route("electives")}}" class="btn btn-default">Exit</a>
                </div>
                <div class="breadcrumb-actions">
                    <a class="btn btn-primary buyelective">Buy Complete Elective for ${{$level->price}}</a>
                </div>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="lessons">

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

        <div class="lessons-title">
            {{$level->name}}
        </div>
        <div class="lessons-description">
            {!! $level->desc_sales !!}
        </div>

        @if($level->getYoutubeCode())
            <div class="elective-presentation">
                <iframe src="https://www.youtube.com/embed/{{$level->getYoutubeCode()}}?autoplay=0&showinfo=0&controls=1&rel=0&hd=1" frameborder="0" allowfullscreen></iframe>
            </div>
        @endif

        <div class="elective-description">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-6">
                        <div class="elective-description-item">
                            <div class="section-title">
                                WHAT’S INCLUDED
                            </div>
                            {!! $level->desc_included !!}
                        </div>


                        @if($level->desc_whofor)
                            <div class="elective-description-item">
                                <div class="section-title">
                                    WHO IS THE {{$level->name}} ELECTIVE FOR?
                                </div>
                                {!! $level->desc_whofor !!}
                            </div>
                        @endif

                    </div>

                    <div class="col-xs-12 col-sm-6">
                        <div class="elective-description-item">
                            <div class="section-title">
                                WE’RE ALWAYS ADDING MORE CONTENT
                            </div>
                            <p>
                                If there’s something {{strtolower($level->name)}}-related you want to know how to talk about that is missing, just let us know, and we’ll almost always add it.
                            </p>
                            <p>
                                Our goal for each paid elective is that you are able to confidently talk about the subject and know all needed vocabulary after completing the elective, so if we’re missing something, we’ll add it.
                            </p>
                            <p>
                                But note, as the size of an elective grows, the price usually goes up. So if you want to get any future updates without paying more, buy this elective now.
                            </p>
                        </div>

                        <div class="elective-description-item">
                            <div class="section-title">
                                WHY BUY ELECTIVES?
                            </div>
                            <p>
                                We require you to take elective classes as part of our BaseLang progress system, because there’s only so much “core” Spanish that everyone needs to know. What’s important beyond a strong base is <b>knowing how to talk about the topics important to you.</b>
                            </p>
                            <p>
                                Over the course of all 10 levels, we require you to take 120 electives. And we provide enough electives included for free in your BaseLang membership to fulfill this without buying any electives.
                            </p>
                            <p>
                                But, if you only use the free electives, you are never going <b>deep</b> on a topic - just brushing the surface of many topics, and probably quite a few topics you don’t care about.
                            </p>
                            <p>
                                By paying for electives, like our {{$level->name}} elective, you can <b>do a deep dive into the topics you care about and truly master it in Spanish.</b>
                            </p>
                            <p>
                                Plus, paid electives <b>include extra lesson types.</b> While free electives only include vocabulary lessons, with the accompanying conversation class, but paid electives also have video lessons, article lessons, and situation lessons.
                            </p>
                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>

    <form id="buy-elective" class="hidden-form" action="{{route("elective_buy")}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" value="{{$level->id}}" name="level_id"/>
    </form>

    <div class="elective-action">
        <div class="elective-action-wrapper">
            <div class="elective-action-title">
                Get the Complete {{$level->name}} Elective Now
            </div>
            <div class="elective-action-description">
                <p>You’ll get access to everything we describe above instantly, and can start learning how to talk about {{$level->name}} in Spanish. Any future content and new lessons we add you will get access to as well, free.</p>
                <p><b>This elective is currently just ${{$level->price}}.</b> This price may increase as we continue to add more lessons to it, so get it now to lock in this price.</p>
            </div>
            <div class="elective-action-actions">
                <button class="btn btn-primary buyelective">Buy {{$level->name}} Elective</button>
            </div>
        </div>
    </div>
@endsection

@section("scripts")
    <script>
        $(document).ready(function(){
            $(".buyelective").click(function () {
                $("#buy-elective").submit();
            })
        })
    </script>
@endsection