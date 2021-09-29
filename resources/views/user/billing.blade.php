@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">

        @if($errors->any())
            @foreach ($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!! $error !!}
                </div>
            @endforeach
        @endif

        @if(session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{ session('message_info') }}
            </div>
        @endif

        <div class="billing-container">
            <div class="billing-title">
                Billing
            </div>
            <div class="billing-desc">
                <p>
                    @if(!$user->is_subscribed)
                        See past transactions, update your billing method, or signup for one of our online programs here.
                    @else
                        change your plan, edit your payment method, or see transaction history here.
                    @endif
                </p>
            </div>
        </div>


        <div class="billing-info">
            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label for="payment_method">
                                Currently On File
                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
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

            @if(!($user->has_immersion && !$user->is_subscribed))
            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label>
                                Location
                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <div class="my-plan-container">
                                @if($user->subscription->plan->location->name=='online')
                                    <h3 class="title-pre"><b>Online</b></h3>
                                    <p id="p-prebook-billing">I want to take my classes online.</p>
                                @else
                                    <h3 class="title-pre"><b>{{ucfirst($user->subscription->plan->location->display_name)}} Medellin</b></h3>
                                    <p id="p-prebook-billing">Take classes in-person at the {{ucfirst($user->subscription->plan->location->display_name)}} Medellin, or online.</p>
                                @endif
                            </div>
                            <a href="{{route('change_location')}}" class="btn btn-primary btn-block">change Location</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Start My Plan -->
            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label>
                                My Plan
                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">

                            <div class="my-plan-container {{$user->subscription->status=='cancelled'?'cancelled':''}}">
                                <img src="{{asset("img/".$user->subscription->plan->type.".png")}}" alt="Plan Logo"/>

                                <div class="my-plan-info">
                                    <div class="plan-title">
                                        {{$user->getCurrentSubscription()->plan->display_name}}
                                    </div>
                                    <div class="plan-price">
                                        @if($user->isOnlineGrandfatheredPlanCancelledOrPaused())
                                            ${{number_format($user->getOnlineRWPlan()->price,0)}}
                                        @else
                                            ${{number_format($user->subscription->plan->price,0)}} 
                                        @endif
                                        @if($user->subscription->plan_name !='grammarless-online-900')
                                            @if(!($user->isInmersionStudent() && $user->subscription->plan->location->name!='online'))
                                                per month 
                                            @endif
                                        @endif
                                    </div>
                                    <div class="plan-features">
                                        @foreach($user->subscription->plan->features as $featured)
                                            <div class="plan-featured">
                                                {{$featured[0]}}
                                                @if(isset($featured[1]))
                                                    <i class="fa fa-question-circle tooltip-im-activator" data-html="true" data-container="body" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="{{$featured[1]}}"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($user->subscription->plan->type=="hourly")
                                        <div class="plan-price">
                                            You have {{$user->credits}} credits available
                                        </div>
                                    @endif

                                </div>
                            </div>

                            @if($user->is_pending && !$user->isReferralEnabled())
								@if(!($user->isInmersionStudent() && $user->subscription->plan->location->name!='online'))
									<a class="btn btn-primary btn-block" data-toggle="modal" data-target="#startnow">Start Now {{$user->subscription->plan->display_name}}</a>
									<a class="btn btn-primary btn-block" data-toggle="modal" data-target="#changedate">change Date of {{$user->subscription->plan->display_name}}</a>

									<div id="startnow" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<form action="{{route("billing_start_now")}}" method="post">
												{{ csrf_field() }}
												<div class="modal-content">
													<div class="modal-header">
														<h4 class="modal-title">Start Subscription</h4>
													</div>
													<div class="modal-body">
														<p>Do you want to start your subscription now?</p>
													</div>
													<div class="modal-footer">
														<button type="submit" class="btn btn-primary btn-block">Confirm</button>
														<button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
													</div>
												</div>
											</form>
										</div>
									</div>

									<div id="changedate" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<form action="{{route("billing_change_start_date")}}" method="post">
												{{ csrf_field() }}
												<div class="modal-content">
													<div class="modal-header">
														<h4 class="modal-title">change Subscription Date</h4>
													</div>
													<div class="modal-body">
														<input class="form-control" data-toggle="datepicker" name="date" value="{{$user->subscription->starts_at->format('Y-m-d')}}">
													</div>
													<div class="modal-footer">
														<button type="submit" class="btn btn-primary btn-block">Confirm</button>
														<button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
													</div>
												</div>
											</form>
										</div>
									</div>
								@endif
                            @endif


                            @if($user->is_subscribed || ($user->subscription->subscription_id == "BaseLang"))
                                <a href="{{route("change_subscription")}}" class="btn btn-primary btn-block">change Plan</a>
                            @endif

                            @if($user->subscription->pause && $user->subscription->pause->format('Y-m-d H:i:s')>gmdate('Y-m-d H:i:s'))
                                <div class="my-plan-container">
                                    <p>You paused and will have access until {{$user->subscription->ends_at->format("F j")}}</p>
                                </div>
                                <a href="{{route("pause_undo")}}" class="btn btn-primary btn-block">Undo Pause</a>
                            @elseif($user->subscription->resume && $user->subscription->resume->format('Y-m-d H:i:s')>gmdate('Y-m-d H:i:s'))
                                <div class="my-plan-container">
                                    <p>Your account will automatically be reactivated in {{$user->subscription->getResumeDays()}} days.</p>
                                </div>

                                <a href="{{route("pause_resume")}}" class="btn btn-primary btn-block">Resume Now</a>
                                <a href="{{route("pause_extend")}}" class="btn btn-primary btn-block">Extend Pause</a>

                                <a href="{{route("pause_cancel")}}" class="btn btn-primary btn-block">Fully Cancellation</a>
                            @endif

                            @if($user->subscription->status=='cancelled' && !in_array($user->subscription->plan_name, ["grammarless-online-900", "grammarless-online-1000paymentplan", "grammarless-medellin-1200", "grammarless-medellin-600"]))
                                <a href="{{route("resubscribe")}}" class="btn btn-primary btn-block">Re-Subscribe</a>
                            @endif
                            @if(($user->subscription->status=='non_renewing' && !in_array($user->subscription->plan_name, ["grammarless-online-900", "grammarless-online-1000paymentplan", "grammarless-medellin-1200", "grammarless-medellin-600"])) || ($user->subscription->status=='in_trial' && $user->subscription->next_billing->format('Y-m-d')<gmdate('Y-m-d')))
                                <div class="my-plan-container">
                                    <p>You cancelled and will have access until {{$user->subscription->ends_at->format("F j")}}</p>
                                </div>
                                <a href="{{route("cancel_undo")}}" class="btn btn-primary btn-block">Undo Cancellation</a>
                                <a href="{{route("cancel_now")}}" class="btn btn-primary btn-block">Cancel Now</a>
                            @endif
                            @if($user->subscription->change)
                                <div class="my-plan-container">
                                    <p>You will {{$user->subscription->future->name=='baselang_hourly'?'downgrade':'switch'}} to {{$user->subscription->future->display_name}} on {{$user->subscription->next_billing->format("F j")}}</p>
                                </div>
                                <a href="{{route("change_now")}}" class="btn btn-primary btn-block">
                                    {{$user->subscription->future->name=='baselang_hourly'?'Downgrade':'Switch'}} To {{$user->subscription->future->display_name}} Now
                                </a>
                                <a href="{{route("change_cancel")}}" class="btn btn-primary btn-block">
                                    Cancel {{$user->subscription->future->name=='baselang_hourly'?'Downgrade':'Switch'}} To {{$user->subscription->future->display_name}}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if($user->next_charge->date && $user->subscription->status != 'non_renewing')
				@if(!($user->isInmersionStudent() && $user->subscription->plan->location->name!='online'))
				<div class="billing-input">
					<div class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-4">
								<label>
									Your Next Charge
								</label>
							</div>
							<div class="col-xs-12 col-sm-8">
								<div class="input-group">
									<input class="form-control" value="{{$user->next_charge->date->format("F d, Y")}}" disabled />
                                    @if($user->subscription->next_payment)
                                        <span class="input-group-addon" id="basic-addon2">$ {{number_format($user->subscription->next_payment,2,'.',' ')}}</span>
                                    @else
                                        <span class="input-group-addon" id="basic-addon2">$ {{number_format($user->next_charge->amount,2,'.',' ')}}</span>
                                    @endif
								 </div>
							</div>
						</div>
					</div>
				</div>
				@endif
            @endif

            @if($user->getMedellinGLNextBillingDate())
				<div class="billing-input">
					<div class="container-fluid">
						<div class="row">
							<div class="col-xs-12 col-sm-4">
								<label>
                                    Your Next Charge
								</label>
							</div>
							<div class="col-xs-12 col-sm-8">
								<div class="input-group">
									<input class="form-control" value="{{$user->getMedellinGLNextBillingDate()->format("F d, Y")}}" disabled />
									<span class="input-group-addon" id="basic-addon2">${{number_format($user->subscription->plan->price/2,0)}}</span>
								 </div>
							</div>
						</div>
					</div>
				</div>
            @endif

            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label>
                                Payment History
                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <div id="payment-history">
                                <img alt="Loading Image" class="lesson-summary-transition" src="{{asset("img/loading-circle.svg")}}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(($user->has_immersion && !$user->is_subscribed))
                <div class="billing-input">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-4">
                                <label>
                                    Online Programs
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-8">
                                <div class="my-plan-container">
                                    <h3 class="title-pre"><b>Online Programs</b></h3>
                                    <p id="p-prebook-billing">In addition to our in-person Immersion medellin, we also have two online programs that offer unlimited one-on-one tutoring. Click below to learn more.</p>
                                </div>
                                <a href="https://baselang.com/online/realworld/" class="btn btn-primary btn-block">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($user->canCancelSubscription() && $user->subscription && !in_array($user->subscription->plan->name,["grammarless-online-1000paymentplan", "grammarless-online-900"]))
            <div class="billing-input">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <label>
                                Cancel
                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-8">
                            <a href="{{route("cancel")}}" class="btn btn-outline btn-danger btn-block">Cancel BaseLang Subscription</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- End My Plan -->

        </div>
    </div>

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

            $("#payment-history").load("{{route("billing_history")}}",function(){
                $('[data-toggle="tooltip"]').tooltip();
            });

            $('[data-toggle="tooltip"]').tooltip();

            $("body").delegate(".load_more_paymenth","click",function () {
                $(this).remove();

                $.get( "{{route("billing_history")}}/"+JSON.stringify($(this).data('offset')), function( data ) {
                    $("#payment-history").append(data);
                });
            })

        })
    </script>
@endsection
