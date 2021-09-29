<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">


        <title>{{isset($page_title)?$page_title:"Buscatupaz.com"}}</title>
        <link rel="stylesheet" href="{{asset("css/app.css?v=42")}}">

        <link rel="shortcut icon" href="{{ asset('img/favicons/favicon.ico?v=2') }}">

        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ asset('img/favicons/apple-touch-icon-144x144-precomposed.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ asset('img/favicons/apple-touch-icon-114x114-precomposed.png') }}">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ asset('img/favicons/apple-touch-icon-72x72-precomposed.png') }}">
        <link rel="apple-touch-icon-precomposed" href="{{ asset('img/favicons/apple-touch-icon-precomposed.png') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body class="page-{{Route::currentRouteName()}}">
        <div class="main-menu">
            <div class="main-menu-header">
                <a href="{{route("dashboard")}}">
                    <img src="{{asset("img/logo-menu.png?v=2")}}" alt="BuscatuPaz.com Logo"/>
                    <span>
{{--
@if(isset($user->id) &&  $user->getCurrentRol()->name=="student" && $user->isInmersionStudent())

    @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan->name=="baselang_hourly")
        {{strtoupper(isset($location) && $location && $location->name?$location->name:'')}} <br> <b>GL + HOURLY</b>
    @elseif($user->getCurrentSubscription() && ($user->getCurrentSubscription()->plan->name=="baselang_99" || $user->getCurrentSubscription()->plan->name=="baselang_129" || $user->getCurrentSubscription()->plan->name=="baselang_149" || $user->getCurrentSubscription()->plan->name=="medellin_RW" || $user->getCurrentSubscription()->plan->name=="medellin_RW_1199"))
        {{strtoupper(isset($location) && $location && $location->name?$location->name:'')}} <br> <b>GL + REAL WORLD</b>
    @elseif($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan->name=="medellin_RW_Lite")
        {{strtoupper(isset($location) && $location && $location->name?$location->name:'')}} <br> <b>GL + REAL WORLD LITE</b>
    @elseif($user->getCurrentSubscription() && ($user->getCurrentSubscription()->plan->name=="baselang_dele" || $user->getCurrentSubscription()->plan->name=="medellin_DELE"))
        {{strtoupper(isset($location) && $location && $location->name?$location->name:'')}} <br> <b>GL + DELE</b>
    @else
        {{strtoupper(isset($location) && $location && $location->name?$location->name:'')}} <br> <b>Grammarless</b>
    @endif

@else

    @if(isset($user->id) && $user->getCurrentRol()->name=="student")
        @if(isset($user->id) && $user->getCurrentSubscription() && $user->getCurrentSubscription()->plan->name=="baselang_hourly")
            ONLINE <br> <b>HOURLY</b>
        @elseif(isset($user->id) && ($user->getCurrentSubscriptionType()=="dele_real" || $user->getCurrentRol()->name=="coordinator"))
            ONLINE <br> <b>RW + DELE</b>
        @elseif(session("current_subscription")=="dele")
            @if(isset($user->id) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_DELE" || $user->subscriptionAdquired()->plan->name=="medellin_RW"))
                MEDELLIN <br> <b>DELE</b>
            @else
                ONLINE <br> <b>DELE</b>
            @endif
        @elseif(session("current_subscription")=="real")
            @if(isset($user->id) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW" || $user->subscriptionAdquired()->plan->name=="medellin_RW_1199" || $user->subscriptionAdquired()->plan->name=="medellin_DELE"))
                MEDELLIN <br> <b>REAL WORLD</b>
            @elseif(isset($user->id) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW_Lite"))
                MEDELLIN<br> <b>REAL WORLD LITE</b>
            @else
                ONLINE <br> <b>REAL WORLD</b>
            @endif
        @endif
    @elseif(isset($user->id) && $user->getCurrentRol()->name=="teacher")
        ONLINE <br> <b>TEACHER</b>
    @elseif(isset($user->id) && $user->getCurrentRol()->name=="admin")
        ONLINE <br> <b>ADMIN</b>
    @elseif(isset($user->id) && $user->getCurrentRol()->name=="coordinator")
        @if(session("current_subscription")=="real")
            ONLINE <br> <b>REAL WORLD</b>
        @else
            ONLINE <br> <b>DELE</b>
        @endif

    @else

    @endif
    @endif
--}}

<b>BuscatuPaz.com</b>

</span>
</a>
<a class="main-menu-responsive-bars">
<img class="inactive-image" src="{{asset("img/menu-open.png")}}" alt="Open Menu"/>
<img class="active-image" src="{{asset("img/menu-close.png")}}" alt="Close Menu"/>
</a>
</div>

@if(isset($user->id) &&$user->getCurrentRol()->name=="coordinator")
<form id="current_subscription_form" method="post" action="{{route("change_type")}}" class="{{session("current_subscription")=="dele"?"dele-selected":"real-selected"}}">
{{csrf_field()}}
<select id="select_current_subscription" >
<option {{session("current_subscription")=="real"?"selected":""}} >REAL WORLD</option>
<option {{session("current_subscription")=="dele"?"selected":""}}>DELE</option>
</select>
<i class="fa fa-angle-down" aria-hidden="true"></i>
</form>
@endif

<div id="class-alarm"></div>
@if(isset($user->id))
@include("includes.menu_".$user->getCurrentRol()->name)
@endif
</div>


<div class="main-content" id="main-content-@if($user->id){{$user->getCurrentRol()->name}}@endif">
@yield("content")
</div>


<div id="ajaxloader"><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></div>

<div id="tooltip-hover"></div>

<script type="text/javascript" src="{{asset("js/app.js?v=41")}}"></script>
<script src="https://js.chargebee.com/v2/chargebee.js"></script>
@yield("scripts")

<script>
var noAjaxLoading=false;

$(document).ready(function () {

@if($user->id&&$user->getCurrentRol()->name=="coordinator")
$("#select_current_subscription").change(function () {
$("#current_subscription_form").submit();
});
@endif

$("#select_current_immersion").change(function () {
$("#current_immersion_form").submit();
});

$(".main-menu-responsive-bars").click(function () {
$(this).toggleClass("active");
$("#menu,.main-menu").toggleClass("active");
});


function loadAlarm(){
noAjaxLoading=true;
$.get( "{{route("class_alarm")}}", function( data ) {
if(data==""){
$("#class-alarm").html("");
$("#class-alarm").removeClass("active");
@if($user->id&&$user->getCurrentRol()->name=="teacher")
$(".main-content").removeClass("teacher-alarm");
@else
$(".main-content").removeClass("alarm");
@endif

} else {
$("#class-alarm").html(data);
$("#class-alarm").addClass("active");
@if($user->id&&$user->getCurrentRol()->name=="teacher")
$(".main-content").addClass("teacher-alarm");
@else
$(".main-content").addClass("alarm");
@endif
}
noAjaxLoading=false;
setTimeout(function(){ loadAlarm(); }, 60000);
});
}


@if($user->id&&$user->isSubscribed() && in_array($user->getCurrentRol()->name,['students','teacher']))
loadAlarm();
@endif

})

</script>

@if(!config('app.debug'))
<!--<script type="text/javascript" async="" src="https://widget.intercom.io/widget/oi4wpijz"></script>
<script>
(function(i,s,o,g,r,a,m){i['ProfitWellObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m);
})(window,document,'script','https://dna8twue3dlxq.cloudfront.net/js/profitwell.js','profitwell');
profitwell('auth_token', '25d34e991fc987730b29921d929c7bbe'); // Your unique Profitwell public API token
profitwell('user_id', '{{$user->chargebee_id}}'); // enter the Customer ID of the logged-in user
</script>

<script>
@if($user->id)
/*window.intercomSettings = {
app_id: 'oi4wpijz',
email: '{{$user->email}}',
name: '{{$user->first_name}} {{$user->last_name}}',
user_id: '{{$user->id}}'
};*/


(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function")
{ic('reattach_activator');ic('update',intercomSettings);}else
{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args)
{i.q.push(args)};w.Intercom=i;function l()
{var s=d.createElement('script');s.type='text/javascript';s.async=true;
s.src='https://widget.intercom.io/widget/oi4wpijz';
var x=d.getElementsByTagName('script')[0];
x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}
else{w.addEventListener('load',l,false);}}})()
@endif
</script>
@endif
-->
</body>
</html>
