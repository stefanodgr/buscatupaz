<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{isset($page_title)?$page_title:"BaseLang"}}</title>
        <link rel="stylesheet" href="{{asset("css/app.css?v=38")}}">

        <link rel="shortcut icon" href="{{ asset('img/favicons/favicon.ico?v=2') }}">

        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ asset('img/favicons/apple-touch-icon-144x144-precomposed.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ asset('img/favicons/apple-touch-icon-114x114-precomposed.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ asset('img/favicons/apple-touch-icon-72x72-precomposed.png') }}">
        <link rel="apple-touch-icon-precomposed" href="{{ asset('img/favicons/apple-touch-icon-precomposed.png') }}">

    </head>
    <body>

        <div class="container-fluid top-elements">
            <div class="row">
                <div class="col-xs-12 col-sm-6 text-left container-btn ">
                    <a id="btn-extra" class="btn btn-default exit">Exit</a>
                </div>
                <div class="col-xs-12 col-sm-6 text-right container-btn">
                    <a id="btn-extra" class="btn btn-primary extra" data-toggle="modal" data-target="#modal-silver-prebook">Buy Prebook Silver for $99</a>
                    <a id="btn-extra" class="btn btn-primary" data-toggle="modal" data-target="#modal-gold-prebook">Buy Prebook Gold for $299</a>
                </div>
            </div>
            <hr>
        </div>

        <div class="container-fluid top-elements">
            <div class="container-content">
                
                <h1>Prebook</h1>
                
                <div class="text-principal-prebook">
                    <div class="p-prebook">
                        <p class="text-content-prebook">With prebook, you can schedule with any teacher ahead of time.</p>

                        <p class="text-content-prebook">For instance, if you prebook 9am on Monday with Carlos, that class will automatically be booked for you before the schedule is released. So not only do you get the teacher you want at the time you want, but you don’t have to manually book the class.</p>

                        <p>Only 25% of any teacher’s time is available for prebook, to ensure all teachers are still accessible by all students. You can see any teacher’s available prebook-able time on <a href="{{route("prebook")}}">the Prebook page</a>.</p>
                    </div>
                </div>

                <br>
                <div class="text-secundary-prebook">
                    <div class="p-prebook">
                        <p class="text-content-prebook"><b>Prebook is paid by the <span class="italic-font">year</span> only.</b></p>

                        <p>It is NOT automically recurring. No surprise charges, ever.</p>
                        <p class="text-content-prebook extra-p">This means you will need to manually buy it again each year, however, if you are with us on a long-term basis like many students.</p>

                        <p><b>This is separate to the main plan you have</b> (currently, {{$plan==null?"Without plan":$plan}}). </p>
                        <p class="text-content-prebook extra-p">It does not affect anything about your current BaseLang subscription, it simply gives you access to an additional feature. If you pause your account or change your plan, nothing happens to Prebook. However, if you fully cancel your account, you will lose Prebook. </p>

                        <p>If you have questions about how this works, click the chat button in the bottom right and we’ll be happy to help :)</p>
                    </div>
                </div>

            </div>

            <br><br><br>
            <div class="container-footer">
                <br><br>
                <div class="row">
                    <div class="col-xs-12 col-sm-6 text-center">
                        <hr class="v"/>
                        <h1>Prebook <a class="btn btn-default btn-silver"><b>S I L V E R</b></a></h1>
                        <br>
                        <div class="p-prebook">
                            <p>Prebook up to <b>5 hours</b> of class each week</p>
                            <p>$99 per year</p>
                        </div>
                        <br>
                        <a id="btn-extra" class="btn btn-primary extra" data-toggle="modal" data-target="#modal-silver-prebook">Buy Prebook Silver for $99</a>
                        <br><br><br>
                    </div>
                    <div class="col-xs-12 col-sm-6 text-center">
                        <h1>Prebook <a class="btn btn-default btn-gold"><b>G O L D</b></a></h1>
                        <br>
                        <div class="p-prebook">
                            <p>Prebook up to <b>15 hours</b> of class each week</p>
                            <p>$299 per year</p>
                        </div>
                        <br>
                        <a id="btn-extra" class="btn btn-primary" data-toggle="modal" data-target="#modal-gold-prebook">Buy Prebook Gold for $299</a>
                        <br><br><br><br>
                    </div>
                </div>
            </div>
            <br><br>
        </div>

        <div id="modal-silver-prebook" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Confirm Prebook Silver</h4>
                    </div>
                    <div class="modal-body">
                        <p>Prebook up to 5 hours of class each week. $99 per year.</p>
                    </div>
                    <div class="modal-footer">
                        <form action="{{route("buy_prebook")}}" method="post" id="route-form">
                            {{csrf_field()}}
                            <div class="input-container">
                                <input type="hidden" name="type" value="silver">
                            </div>

                            <div class="instant instant-option">
                                <button type="submit" class="btn btn-primary btn-block">Confirm</button>
                            </div>
                        </form>
                        <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-gold-prebook" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Confirm Prebook Gold</h4>
                    </div>
                    <div class="modal-body">
                        <p>Prebook up to 15 hours of class each week. $299 per year.</p>
                    </div>
                    <div class="modal-footer">
                        <form action="{{route("buy_prebook")}}" method="post" id="route-form">
                            {{csrf_field()}}
                            <div class="input-container">
                                <input type="hidden" name="type" value="gold">
                            </div>

                            <div class="instant instant-option">
                                <button type="submit" class="btn btn-primary btn-block">Confirm</button>
                            </div>
                        </form>
                        <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="{{asset("js/app.js?v=38")}}"></script>
        <script type="text/javascript" async="" src="https://widget.intercom.io/widget/oi4wpijz"></script>
        <script>
            window.intercomSettings = {
                app_id: 'oi4wpijz'
            };
        </script>
        <script>
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/oi4wpijz';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()
        </script>
        <script>
            $(document).ready(function () {
                var numberEntries = window.history.length;

                $(".exit").click(function() {
                    if(numberEntries<=2){
                        location.href ="/billing";
                    }else{
                        window.history.back();
                    }
                });

            });
        </script>
    </body>
</html>
