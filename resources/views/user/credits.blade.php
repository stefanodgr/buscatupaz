@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="credits">

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
        <div class="credits-title">
            <h1>Get Credits</h1>
        </div>

        <div class="credits-description">
            <p>There are two ways to get more class credits:</p>

            <ol>
                <li>Book classes and pay while booking the class</li>
                <li>Buy credits in bulk on this page to save money</li>
            </ol>

            <p><b>The more credits you buy at a time, the lower the hourly rate will be.</b></p>
        </div>

        <div class="rangeprice">
            <input type="range" value="20" min="1" step="1" max="100" data-rangeSlider>
            <div class="output">
                <output></output> Credits
                <div class="hours"><span></span> Hours Total</div>
                <div class="price">$<span></span>/hour</div>
            </div>
        </div>

        <div class="buycredits">

            <form method="post" id="chargebee-payment-upgrade-form" action="{{route("upgrade_subscription")}}">
                {{ csrf_field() }}
            </form>

            <form method="post" id="chargebee-payment-credits-form" action="{{route("buy_credits")}}">
                {{ csrf_field() }}
                <input type="hidden" name="valuetobuy" id="valuetobuy" value="20"/>
            </form>
            <button id="buycreditsbutton" class="btn btn-plain btn-primary btn-lg buy-btn">Buy <span class="credits"></span> Credits for $<span class="price"></span></button>

        </div>

        <div class="upgrade">
            <h2>Or, Upgrade to Unlimited</h2>

            <p>Upgrade your account to Real World, which has unlimited classes for a flat rate, and don’t worry about paying per class anymore. <b>It’s just {{($user->last_unlimited_subscription=="baselang_99")?"$99":($user->last_unlimited_subscription=="baselang_129"?"$129":"$149")}} a month.</b> Everything else is as you’re used to.</p>
            <button id="upgradebutton" class="btn btn-plain btn-primary btn-lg buy-btn">Upgrade to Real World</button>
        </div>

            <div class="modal fade" tabindex="-1" role="dialog" id="confirmpurchase">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <div class="confirm-credits">
                                    Confirm Purchase
                                </div>
                                <div class="confirm-upgrade">
                                    Confirm Upgrade
                                </div>
                            </h4>
                        </div>
                        <div class="modal-body modal-buy">
                            <div class="confirm-credits">
                                <p>Buy <span class="credits"></span> Credits for $<span class="price"></span>.</p>
                                <button type="button" class="btn btn-plain btn-primary btn-lg btn-loading" id="continuebuycredits">Continue for $<span class="price"></span></button>
                                <button type="button" class="btn btn-plain btn-default btn-lg" data-dismiss="modal">Cancel</button>
                            </div>
                            <div class="confirm-upgrade">
                                <p>Upgrade to Real World, <b>just {{($user->last_unlimited_subscription=="baselang_99")?"$99":($user->last_unlimited_subscription=="baselang_129"?"$129":"$149")}} a month.</b></p>
                                <button type="button" class="btn btn-plain btn-primary btn-lg btn-loading" id="continueupgrade">Continue for {{($user->last_unlimited_subscription=="baselang_99")?"$99":($user->last_unlimited_subscription=="baselang_129"?"$129":"$149")}}</button>
                                <button type="button" class="btn btn-plain btn-default btn-lg" data-dismiss="modal">Cancel</button>
                            </div>

                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

    </div>

@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/rangeslider.min.js")}}"></script>
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();

            var selector = '[data-rangeSlider]',elements = document.querySelectorAll(selector);

            // Example functionality to demonstrate a value feedback
            function valueOutput(element) {
                var value = element.value,
                    output = element.parentNode.getElementsByTagName('output')[0];
                output.innerHTML = value;
            }

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

            for (var i = elements.length - 1; i >= 0; i--) {
                valueOutput(elements[i]);
            }

            Array.prototype.slice.call(document.querySelectorAll('input[type="range"]')).forEach(function (el) {
                el.addEventListener('input', function (e) {
                    valueOutput(e.target);
                }, false);
            });

            // Basic rangeSlider initialization
            rangeSlider.create(elements, {

                // Callback function
                onInit: function () {
                    $(".rangeprice .output").css('left','20%');
                    $(".rangeprice .output .hours span").text('10');
                    $(".rangeprice .output .price span").text('8');

                    $("#valuetobuy").val('20');
                    $(".modal-buy .credits,.buycredits .buy-btn .credits").text('20');
                    $(".modal-buy .price,.buycredits .buy-btn .price").text('80');
                },

                // Callback function
                onSlideStart: function (value, percent, position) {
                    console.info('onSlideStart', 'value: ' + value, 'percent: ' + percent, 'position: ' + position);
                },

                // Callback function
                onSlide: function (value, percent, position) {

                    if(percent>=(75/$(".rangeprice").width()) && percent<=(1 - (75/$(".rangeprice").width()))){
                        $(".rangeprice .output").css("left",percent*100+'%');
                    }
                    $("#valuetobuy").val(value);
                    $(".rangeprice .output .hours span").text((value/2));
                    $(".rangeprice .output .price span").text(calculePrice(value));
                    $(".modal-buy .credits,.buycredits .buy-btn .credits").text(value);
                    var price = calculePrice(value)*(value/2);

                    if(!Number.isInteger(price)){
                        price=price.toFixed(2)
                    }
                    $(".modal-buy .price,.buycredits .buy-btn .price").text(price);

                    //console.log('onSlide', 'value: ' + value, 'percent: ' + percent, 'position: ' + position);
                },

                // Callback function
                onSlideEnd: function (value, percent, position) {
                    console.warn('onSlideEnd', 'value: ' + value, 'percent: ' + percent, 'position: ' + position);
                }
            });

            $("#buycreditsbutton").click(function () {
                $(".confirm-upgrade").hide();
                $(".confirm-credits").show();
                $("#confirmpurchase").modal()
            });

            $("#upgradebutton").click(function () {
                $(".confirm-credits").hide();
                $(".confirm-upgrade").show();
                $("#confirmpurchase").modal()
            });

            $("#continueupgrade").click(function () {
                $("#chargebee-payment-upgrade-form").submit();
                $("#confirmpurchase").modal('hide');
                $( "#overlayloading" ).show().css("display", "table");;
            });

            $("#continuebuycredits").click(function () {
                $("#chargebee-payment-credits-form").submit();
                $("#confirmpurchase").modal('hide');
                $( "#overlayloading" ).show().css("display", "table");;
            })

        })
    </script>
@endsection