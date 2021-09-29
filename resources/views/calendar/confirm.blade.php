@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("classes_new")}}">
                    {{ __('Select Times') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item chooseteachers">
                    {{ __('Choose Teachers') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    {{ __('Confirm Classes') }}
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
                {{ __('Confirm Classes') }}
            </div>
            <div class="calendar-container-desc">
                {{ __('Please double check everything looks good and then click the confirmation button to finish booking') }}.
            </div>

            <div class="classes-confirm">
                @foreach($classes as $class)
                    @if($class[0])
                        <div class="class-confirm" class-info="{{$class[0]->format("Y-m-d H:i:s")}},{{$class[1]->id}}">
                            <img src="{{asset("assets/users/photos/".$class[1]->id.".jpg?v=".rand())}}" alt="{{$class[1]->first_name}}" />
                            <div class="teacher_name">
                                {{$class[1]->first_name}}
                            </div>
                            <div class="teacher_time">
                                @if($class[0]->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d"))
                                    {{ __('Today at') }} {{$class[0]->format("h:ia")}}
                                @elseif($class[0]->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->add(new DateInterval('P1D'))->format("Y-m-d"))
                                    {{ __('Tomorrow at') }} {{$class[0]->format("h:ia")}}
                                @else
                                    {{$class[0]->format("l d")}} at {{$class[0]->format("h:ia")}}
                                @endif
                            </div>
                            <div class="cancel-class">
                                {{ __('Delete') }}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly")
                <div id="credits_summary">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">Credits needed:</div>
                            <div class="col-sm-6 col-xs-12 credits-needed">{{count($classes)}} Credit{{count($classes)=="1"?"":"s"}}</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">You have:</div>
                            <div class="col-sm-6 col-xs-12">{{$user->credits}} Credit{{$user->credits=="1"?"":"s"}}</div>
                        </div>

                        <div class="row total-buy total-summary {{count($classes)-$user->credits>0?"active":""}}">
                            <div class="col-sm-6 col-xs-12">Total to buy now:</div>
                            <div class="col-sm-6 col-xs-12 credits-to-buy">{{count($classes)-$user->credits}} Credit{{count($classes)-$user->credits=="1"?"":"s"}}</div>
                        </div>

                        <div class="row total-remain total-summary {{count($classes)-$user->credits<=0?"active":""}}">
                            <div class="col-sm-6 col-xs-12">Total:</div>
                            <div class="col-sm-6 col-xs-12 credits-to-remain">{{$user->credits-count($classes)}} Credit{{$user->credits-count($classes)=="1"?"":"s"}}</div>
                        </div>

                    </div>
                </div>

                <div class="modal fade" tabindex="-1" role="dialog" id="confirmupgrade">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <div class="confirm-upgrade">
                                        Confirm Upgrade
                                    </div>
                                </h4>
                            </div>
                            <div class="modal-body modal-buy">
                                <div class="confirm-upgrade">
                                    <p>Upgrade to Real World, <b>just {{($user->last_unlimited_subscription=="baselang_99")?"$99":($user->last_unlimited_subscription=="baselang_129"?"$129":"$149")}} a month.</b></p>
                                    <form method="post" id="chargebee-payment-upgrade-form" action="{{route("upgrade_subscription")}}">
                                        {{ csrf_field() }}

                                        <button type="submit" class="btn btn-plain btn-primary btn-lg btn-loading submitcalendar">Continue for {{($user->last_unlimited_subscription=="baselang_99")?"$99":($user->last_unlimited_subscription=="baselang_129"?"$129":"$149")}}</button>
                                        <button type="button" class="btn btn-plain btn-default btn-lg" data-dismiss="modal">Cancel</button>
                                    </form>
                                </div>

                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <div class="modal fade" tabindex="-1" role="dialog" id="confirmpurchase">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">
                                    <div class="confirm-credits">
                                        Confirm Purchase
                                    </div>
                                </h4>
                            </div>
                            <div class="modal-body modal-buy">
                                <div class="confirm-credits">
                                    <p>Buy <span class="credits"></span> Credits for $<span class="price"></span>.</p>
                                    <button type="button" class="btn btn-plain btn-primary btn-lg btn-loading submitcalendar">Continue for $<span class="price"></span></button>
                                    <button type="button" class="btn btn-plain btn-default btn-lg" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

            @endif

        </div>

        <div id="book-actions" class="confirm-action">
            <form id="post-calendar" action="{{route('save_classes')}}" method="post">
                {{ csrf_field() }}
                @if($location_id)
                    <input type="hidden" name="location_id" value="{{$location_id}}">
                @endif
            </form>
            @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly")
                <button type="button" class="submitcalendarhourly btn btn-primary"></button>
                <button type="button" class="chooseteachers btn btn-default">{{ __('Back') }}</button>
            @else
                <button type="button" class="submitcalendar btn btn-primary">{{ __('Confirm Classes') }}</button>
                <button type="button" class="chooseteachers btn btn-default">{{ __('Back') }}</button>
            @endif

        </div>

        @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly")
            <div id="buy-bulk">
                <div class="buy-bulk-container">
                    <p>Want to save money? <a href="{{route("credits")}}">Buy credits in bulk</a> or <a data-toggle="modal" data-target="#confirmupgrade">upgrade to Real World.</a></p>

                    <p>If you cancel a class before it starts, you will be given a credit for each class cancelled. And of course, if there’s any issue with a class, we’ll make it right.</p>
                </div>
            </div>
        @endif
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

            @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly")
                function calculePrice(Q) {
                    if(Q<=15){
                        return 9;
                    } else if(Q<=30){
                        return 8;
                    } else if(Q<=45){
                        return 7;
                    } else {
                        return 6;
                    }
                }



                function calculeCredits(){
                    var classes=$(".class-confirm").length;
                    var userCredits="{{$user->credits}}";


                    $("#credits_summary .credits-needed").html(classes+" Credit"+(classes==1?"":"s"));
                    $("#credits_summary .credits-to-buy").html((classes-userCredits)+" Credit"+(classes-userCredits==1?"":"s"));
                    $("#credits_summary .credits-to-remain").html((userCredits-classes)+" Credit"+(classes-userCredits==1?"":"s"));

                    if(classes>userCredits && userCredits!=0){
                        //Use x credits and buy y
                        $(".submitcalendarhourly").text("Use "+userCredits+" Credits and Pay $"+(calculePrice(classes-userCredits)*(classes-userCredits)/2).toFixed(2));
                        $("#credits_summary .total-buy").addClass("active");
                        $("#credits_summary .total-remain").removeClass("active");


                    } else if(userCredits==0){
                        //Buy Y
                        $(".submitcalendarhourly").text("Pay $"+(calculePrice(classes-userCredits)*(classes-userCredits)/2).toFixed(2));
                        $("#credits_summary .total-buy").addClass("active");
                        $("#credits_summary .total-remain").removeClass("active");
                    } else {
                        //Use x credits
                        $(".submitcalendarhourly").text("Use "+classes+" Credits");
                        $("#credits_summary .total-buy").removeClass("active");
                        $("#credits_summary .total-remain").addClass("active");

                    }

                    $("#confirmpurchase .credits").html(classes-userCredits);
                    $("#confirmpurchase .price").html((calculePrice(classes-userCredits)*(classes-userCredits)/2).toFixed(2));

                }

                calculeCredits();
            @endif


            $(".submitcalendarhourly").click(function () {
                var classes=$(".class-confirm").length;
                var userCredits="{{$user->credits}}";

                if(classes>userCredits){
                    //show modal
                    $('#confirmpurchase').modal('show');
                } else {
                    $.each($( ".class-confirm" ),function(k,v){
                        $("#post-calendar").append('<input type="hidden" value="'+$(v).attr("class-info")+'" name="selected[]"/>');
                    })
                    $("#post-calendar").submit();
                    $("#book-actions .submitcalendarhourly").prop('disabled', true);
                }
            });

            $(".submitcalendar").click(function () {
                $.each($( ".class-confirm" ),function(k,v){
                    $("#post-calendar").append('<input type="hidden" value="'+$(v).attr("class-info")+'" name="selected[]"/>');
                })
                $("#post-calendar").submit();
                $("#book-actions .submitcalendar").prop('disabled', true);
            });

            $(".cancel-class").click(function () {
                $(this).parent().remove();

                @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan_name=="baselang_hourly")
                    calculeCredits();
                @endif


                if($(".cancel-class").length==0){
                    window.location.replace("{{route("classes_new")}}");
                }
            });

            $(".chooseteachers").click(function () {
                window.history.back()
            });



        })
    </script>
@endsection