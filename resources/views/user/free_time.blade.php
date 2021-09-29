@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="free_time">

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

        <div class="free-time-container">
            <div class="free-time-title">
                Get Free Time With BaseLang
            </div>
            <div class="free-time-desc">
                <p>
                    Love BaseLang but want it free (or simply can’t afford to pay)? Here are the options you have to get some free time for yourself.
                </p>
            </div>
        </div>

        <div class="content-free-time">
            <div class="content-dashboard-title">
                Refer Your Friends
            </div>
            <div class="content-dashboard-subtitle">
                FREE TIME: ONE MONTH
            </div>
            <div class="content-dashboard-desc">
                <p>You can get free time by referring your friends to BaseLang!</p>
                <p>Just send them your unique link:</p>

                <div id="referalinput">

                    <input type="text" id="referallink" value="baselang.com/signup/?referral={{(urlencode($user->email))}}" data-placement="bottom" disabled="">
                    <span class="add-on" data-toggle="tooltip" title="Copied!">Copy</span>
                </div>

                <p>
                    <i>Share this link on social media:</i>
                </p>

                <div id="referalsocialmedia">
                    <a onClick="window.open('https://www.facebook.com/sharer/sharer.php?u={{urlencode("https://baselang.com/realworld/?referral=".urlencode($user->email))}}','Facebook Share','resizable,height=260,width=370'); return false;" class="facebookshare sociallink">
                        <i class="fa fa-facebook-square" aria-hidden="true"></i>
                    </a>
                    <a onClick="window.open('https://twitter.com/intent/tweet?text={{urlencode("I've been using this now for awhile to learn Spanish and highly recommend it if you want to get conversational fast")}}&url={{urlencode("https://baselang.com/realworld/?referral=".urlencode($user->email))}}','Twitter Share','resizable,height=260,width=370'); return false;" class="twittershare sociallink">
                        <i class="fa fa-twitter" aria-hidden="true"></i>
                    </a>
                </div>

                <p>
                    When someone clicks your link, they will be cookied, and if they signup within 30 days of clicking your link, you’ll get credit for referring them. After they finish their trial and become a paying student, you’ll get one month of whatever plan you’re on, free!
                </p>
                <p>
                    There are no limits on this. Refer five friends? You’ll get five months of unlimited classes! If money is tight you could cover all of your classes by referring friends.
                </p>
                <p>
                    If your friend cancels or downgrades to Hourly before the end of their trial, you don’t get any free time.
                </p>
                <p>
                    <i>
                    Please note that abuse of this is monitored and results in a permanent ban for everyone involved. Full terms can be read <a href="https://baselang.com/terms/"><i>here</i></a>.
                    </i>
                </p>

            </div>
        </div>

        @if((gmdate("Y-m-d")>DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new DateInterval("P30D"))->format("Y-m-d") && session("current_subscription")=="real") || (gmdate("Y-m-d")>DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new DateInterval("P37D"))->format("Y-m-d") && session("current_subscription")=="dele"))
            <div class="content-free-time">
                <div class="content-dashboard-title">
                    Write An In-Depth Review
                </div>
                <div class="content-dashboard-subtitle">
                    FREE TIME: ONE MONTH
                </div>
                <div class="content-dashboard-desc">
                    <p>
                        Reviews are extremely valuable for people deciding if they want to give BaseLang a shot or not. If you love BaseLang and want to help us help more people, writing an in-depth review is a great way to go.
                    </p>
                    <p>
                        You can publish it on your own blog if you have one, on ours, or on a third-party site like Medium.com. We just want it to be at least 1000 words, and honestly cover your experience with us so far.
                    </p>
                    <p>
                        Protip: combine this with the referral setup, and get lots of free time!
                    </p>
                    <p>
                        For this, please <a href="mailto:niall@baselang.com">get in touch with Niall</a> who will help with any questions you have, and then hook you up with the free time after it’s live.
                    </p>

                </div>
            </div>
        @endif

        @if((gmdate("Y-m-d")>DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new DateInterval("P30D"))->format("Y-m-d") && session("current_subscription")=="real") || (gmdate("Y-m-d")>DateTime::createFromFormat("Y-m-d",$user->created_at->format("Y-m-d"))->add(new DateInterval("P37D"))->format("Y-m-d") && session("current_subscription")=="dele"))
            <div class="content-free-time">
                <div class="content-dashboard-title">
                    Short Video Testimonial
                </div>
                <div class="content-dashboard-subtitle">
                    FREE TIME: ONE MONTH
                </div>
                <div class="content-dashboard-desc">
                    <p>
                        Like a review, but way shorter and video. Minimum of one minute, but you can shoot this with your webcam. Just an honest recount of your experience and progress with us, and what that has done for you.
                    </p>
                    <p>
                        Preferably, uploaded to YouTube or Facebook!
                    </p>
                    <p>
                        For this, please <a href="mailto:niall@baselang.com">get in touch with Niall</a> who will help with any questions you have, and then hook you up with the free time after it’s live.
                    </p>

                </div>
            </div>
        @endif

        <div class="content-free-time">
            <div class="content-dashboard-title">
                Have Another Idea?
            </div>
            <div class="content-dashboard-subtitle">
                FREE TIME: ???
            </div>
            <div class="content-dashboard-desc">
                <p>
                    Have an idea for something not listed here? We’d love to hear it!
                </p>
                <p>
                    Preferably, uploaded to YouTube or Facebook!
                </p>
                <p>
                    <a href="mailto:niall@baselang.com">Get in touch with Niall</a> to share your idea, and we’ll go from there.
                </p>

            </div>
        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $("#referalinput, #referalinput span").click(function(){
                copyToClipboard('https://'+$("#referalinput input").val())

                $('#referalinput span').tooltip("enable");
                $('#referalinput span').tooltip("show");


                setTimeout(function(){ $('#referalinput span').tooltip('disable') }, 2000);


            });

            function copyToClipboard(message) {
                var aux = document.createElement("input");
                aux.setAttribute("value",message);
                document.body.appendChild(aux);
                aux.select();
                document.execCommand("copy");
                document.body.removeChild(aux);

            }
        })
    </script>
@endsection