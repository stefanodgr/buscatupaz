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
    <body id="body-inmersion">

        @include("inmersion.includes.menu_".$menu)

        <br><br>
        <div class="container">
            @yield("content")
        </div>

        <div id="ajaxloader">
            <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
        </div>

        <script type="text/javascript" src="{{asset("js/app.js?v=38")}}"></script>
        @yield("scripts")
        <script>
            var noAjaxLoading=false;
        </script>
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
