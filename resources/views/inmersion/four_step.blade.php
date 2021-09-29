@extends("layouts.inmersion")

@section("content")
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <h1 class="text-left title-pick margin-title">Pay Deposit</h1>
    <p class="text-inmersion inm-margin">Thanks, {{$user->first_name}}. Now, we need a 50% deposit to reserve your spot.</p>
    <p class="text-inmersion inm-margin">The deposit is <b>non-refundable</b>, but in case you can’t make it, we can count it towards a future course or credit for our online programs.</p>
    <p class="text-inmersion inm-margin" id="scroll-btn"><b>Your dates being reserved:</b></p>

    @foreach($selecteds as $key => $selected)
        @php 
            $sel=explode(",",$selected);
        @endphp
        <p class="text-inmersion">{{DateTime::createFromFormat("Y-m-d", $sel[0])->format("M d")}} - @if(DateTime::createFromFormat("Y-m-d", $sel[0])->format("m") != DateTime::createFromFormat("Y-m-d", $sel[1])->format("m")) {{DateTime::createFromFormat("Y-m-d", $sel[1])->format("M d")}}, @else {{DateTime::createFromFormat("Y-m-d", $sel[1])->format("d")}}, @endif {{DateTime::createFromFormat("Y-m-d", $sel[0])->format("Y")}}, @if($sel[2]=="AM") morning classes (8:30am to 12:30pm) @else afternoon classes (1:30pm to 5:30pm) @endif with {{$teachers[$key]->first_name}}</p>
    @endforeach
    <br><br>

    <div id="container-third-step">
        <br><br>
            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12">
                            @if($user->card_last_four)
                                <div class="card-container">
                                    <span class="icon-container">
                                        <i class="fa fa-cc-{{$user->pay_image}}"></i>
                                    </span>
                                    <input id="payment_method" class="form-control left-image payment-image-{{$user->pay_image}}" value="{{$user->card_last_four}}" disabled />
                                </div>
                            @elseif($user->paypal_email)
                                <div class="card-container">
                                    <span class="icon-container">
                                        <i class="fa fa-cc-{{$user->pay_image}}"></i>
                                    </span>
                                    <input id="payment_method" class="form-control left-image payment-image-{{$user->pay_image}}" value="{{$user->paypal_email}}" disabled />
                                </div>
                            @else
                                <input id="payment_method" class="form-control" value="No Payment Method" disabled />
                            @endif
                            <a href="#" id="edit_payment_method" class="btn btn-primary btn-block">{{$user->card_last_four?'Edit':'Create'}} Payment Method</a>
                        </div>
                    </div>
                </div>
            </div>
        <br><br>
        
        <form id="form-update-pay" method="post" action="{{route('update_card_inmersion')}}">
            <input type="hidden" name="location_id" value="{{$location->id}}">
            <div class="billing-info" @if($user->card_last_four || $user->paypal_email) style="display: none;" @endif>
                <div class="billing-input">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-8">
                                <div class="chargebee-payment-form">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="location_id" value="{{$location->id}}">
                                    @foreach($selecteds as $selected)
                                        <input type="hidden" value="{{$selected}}" name="selecteds[]"/>
                                    @endforeach
                                    <br>
                                </div>
                                <div>
                                    <p class="text-inmersion text-center text-size"><i class="fa fa-lock" aria-hidden="true"></i> Bank-level 256-bit encryption. Your information is secure.</p>
                                    <p class="text-inmersion text-center text-size">By signing up, you agree to our <a href="#">Terms of Service</a>.</p>
                                </div><br><br>
                            </div>
                            <div class="hidden-xs col-sm-4">
                                <div class="card_helps">
                                    <div class="card_help">
                                        <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Click this button to use PayPal
                                    </div>
                                    <div class="card_help by_card">
                                        <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Or enter your card info here to pay by card
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <br><br>

            <button type="submit" class="btn btn-primary btn-next-step third-extra pay-one" @if(!($user->card_last_four || $user->paypal_email)) disabled @endif>Pay ${{$price}} and Reserve Spot</button><br>

            <button type="button" style="display: none;" class="btn btn-primary btn-next-step third-extra pay-two">Pay ${{$price}} and Reserve Spot</button><br>

        </form>

    @if($total_cost_flag)
    <p class="text-inmersion inm-margin" id="text-italic">This is 50% of the total cost, ${{$price*2}}.</p>
    <p class="text-inmersion inm-margin" id="text-italic">The second half will be charged one week before your course starts.</p>
	@endif
    <br><br><br><br><br>

    <form id="post-success" action="{{route('successful_inmersion')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="location_id" value="{{$location->id}}">
    </form>

    <form id="back-to-second" action="{{route('pick_your_teacher')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="location_id" value="{{$location->id}}">
        <input type="hidden" value="{{$selected}}" name="selecteds[]"/>
    </form>

    <form id="back-to-third" action="{{route('your_basic_info')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="location_id" value="{{$location->id}}">
        <input type="hidden" value="{{$selected}}" name="selecteds[]"/>
    </form>

    <style type="text/css">

        @media (max-width: 767px) {
            .third-extra {
                width: 250px !important; 
                height: 50px !important;
                font-size: 16px !important;
            }
        }

    </style>

@endsection

@section("scripts")
    <script src="https://js.chargebee.com/v2/chargebee.js"></script>
    <script>
        $(document).ready(function () {
            
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            Chargebee.init({
                    site: "{{Config::get('services.chargebee.site')}}"
            });

            var cbInstance = Chargebee.getInstance();

            $("#edit_payment_method").click(function () {
                cbInstance.setPortalSession(function(){
                    return $.ajax({
                        url: '{{route('chargebee_session')}}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                });

                var cbPortal = cbInstance.createChargebeePortal();
                cbPortal.openSection({
                    sectionType: Chargebee.getPortalSections().PAYMENT_SOURCES
                },{
                    close: function() {
                        $("#ajaxloader").addClass("active");
                        location.reload();
                    }
                });
            });

            $(".check-opt").click(function () {
                $(".pay-one").prop('disabled', false);

                checked = $('input[name="check"]:checked').val();
                if(checked==2) {
                    $(".billing-info").show();
                    $(".pay-one").show();
                    $(".pay-two").hide();
                }else{
                    $(".billing-info").hide();
                    $(".pay-two").show();
                    $(".pay-one").hide();
                }
            });

            // $("#form-update-pay").closest('form').on('submit', function(e) {
            //     e.preventDefault();
                //$('.pay-one').attr('disabled', true);
            // });

            $(".pay-two").click(function () {

                var selecteds = [];
                @foreach($selecteds as $selected)
                    selecteds.push("{{$selected}}");
                @endforeach

                $.post("{{route("pay_inmersion")}}", {
                    "_token": "{{csrf_token()}}",
                    "location_id": "{{$location->id}}",
                    "selecteds": selecteds,
                }, function (data) {
                    if(data.response=="weeks_zero"){
                        alert("The week(s) you have selected have already been filled by other students, try to schedule again!");
                        location.href ="{{route('inmersion',['location'=>$location->name])}}"; 
                    }else if(data.response=="success") {
                        $(".pay-two").prop('disabled', true);
                        $("#post-success").submit();
                    }else if(data.response=="error") {
                        alert("Your payment was declined. We recommend trying to sign up again using a different credit card and/or phoning your bank to see why the payment was declined.");
                    }
                });

            });

            $(".pay-scroll").click(function (e) {
                e.preventDefault();
                $('html,body').animate({
                    scrollTop: $("#scroll-btn").offset().top
                }, 1500);
            });

            $(".btn-to-second").click(function () {
                $("#back-to-second").submit();
            });

            $(".btn-to-third").click(function () {
                $("#back-to-third").submit();
            });

            $(".main-menu-responsive-bars").click(function () {
                $(this).toggleClass("active");
                $("#menu,.main-menu").toggleClass("active");
            });
        })
    </script>
@endsection