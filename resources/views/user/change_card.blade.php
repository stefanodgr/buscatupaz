@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    change Payment Method
                </a>

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="billing">

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

        <div class="billing-container">
            <div class="billing-title">
                change Payment Method
            </div>
            <div class="billing-desc billing-light-desc">
                <p>
                    Update your payment method here. This will not charge your card, only update what payment method will be used for future charges.
                </p>
                <p>
                    Don’t want to change it? <a href="{{route("billing")}}">Click here to go back</a>.
                </p>
            </div>
        </div>

        <div class="billing-info">
            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-8">
                            <form method="post" class="chargebee-payment-form" action="{{route("billing_update_card")}}">
                                {{ csrf_field() }}
                                <section>
                                    <div class="bt-drop-in-wrapper">
                                        <div id="bt-dropin"></div>
                                    </div>
                                </section>
                                <div class="actionbuttons">
                                    <button class="btn btn-primary" type="submit">{{ __('Save') }} New Payment Method</button>
                                </div>

                            </form>
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

    </div>

@endsection

@section("scripts")
    <script type="text/javascript" src="{{asset("js/jquery.payment.min.js")}}"></script>
    <script src="https://js.braintreegateway.com/js/braintree-2.27.0.min.js"></script>

    <script>
        $(document).ready(function () {
            var client_token = "{{Chargebee\ClientToken::generate()}}";
            chargebee.setup(client_token, "dropin", {
                container: "bt-dropin",
                onReady: function(){
                    $(".card_helps").show();
                },
                onError: function (obj) {
                    if (obj.type == 'VALIDATION') {
                        // Validation errors contain an array of error field objects:
                        obj.details.invalidFields;

                    } else if (obj.type == 'SERVER') {
                        // If the customer's browser cannot connect to Chargebee:
                        obj.message; // "Connection error"

                        // If the credit card failed verification:
                        obj.message; // "Credit card is invalid"
                        obj.details; // Object with error-specific information

                    }
                    alert('Error with your payment information. '+ obj.message);
                }
            });
        });
    </script>
@endsection
