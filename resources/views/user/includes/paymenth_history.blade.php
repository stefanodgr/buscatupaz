@if(count($payments)==0)
    <div class="no-records">
        No records found.
    </div>

@endif

@foreach($payments as $payment)
    <div class="payment">
        <div class="payment-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-6 pay-time">
                        {{\DateTime::createFromFormat('U',$payment->transaction()->date)->format('F d, Y')}}
                    </div>
                </div>
            </div>
        </div>
        <div class="payment-bottom">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-6 pay-info">
                        @if(isset($payments->sources[$payment->transaction()->paymentSourceId]) && $payment->transaction()->paymentSourceId)
                            @if($payments->sources[$payment->transaction()->paymentSourceId]->card)
                                <div class="icon-container">
                                    <i class="fa fa-cc-{{$payments->sources[$payment->transaction()->paymentSourceId]->card->brand}}"></i>
                                </div>
                                {{$payments->sources[$payment->transaction()->paymentSourceId]->card->last4}}
                            @elseif($payments->sources[$payment->transaction()->paymentSourceId]->paypal)
                                <div class="icon-container">
                                    <i class="fa fa-cc-{{$payments->sources[$payment->transaction()->paymentSourceId]->paypal->object}}"></i>
                                </div>
                                {{$payments->sources[$payment->transaction()->paymentSourceId]->paypal->email}}
                            @endif
                        @endif
                    </div>
                    <div class="col-xs-6 pay-amount type-{{$payment->transaction()->type}}">
                        $ {{$payment->transaction()->type=='refund'?'('.($payment->transaction()->amount/100).')':$payment->transaction()->amount/100}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
@if(count($payments)>0 && $payments->nextOffset())
    <div class="load_more_paymenth" data-offset="{{$payments->nextOffset()}}">Show 3 More <i class="fa fa-angle-down" aria-hidden="true"></i></div>
@endif
