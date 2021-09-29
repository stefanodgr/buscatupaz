@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                @if(!$extend)
                <a class="breadcrumb-item" href="{{route("cancel")}}">
                    Cancel <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("cancel")}}">
                    I'm going to take a break, I'll be back! <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Pause Account
                </a>
                @else
                    <a class="breadcrumb-item">
                        Extend Pause
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">

        <div class="billing-container">
            <div class="billing-title">
                {{$extend?'Extend Pause':'Pause Account'}}
            </div>
            <div class="cancel-desc">
                <p>
                    Pick how long you'd like to pause for. We'll email you 3 days before the restart date to ensure you're still ready (you'll be able to extend again from that email, if you need to), no surprise charges.
                </p>
                @if(!$extend)
                    <p>
                        The pause will start once the time you have already paid for runs out-you'll still have access until then. For you, that's {{$user->subscription->ends_at->format('F j, Y')}}.
                    </p>
                @endif
            </div>
            <div class="container-fluid">
                <div class="row">
                    <form action="{{route($extend?"pause_extend":"pause_account")}}" method="post">
                        {{csrf_field()}}
                        <select class="form-control" name="activation_day">
                            @foreach($pause_options as $k=>$option)
                                @if($extend)
                                    <option value="{{$k}}">{{$option}} (Restart on {{$user->subscription->resume->add(new DateInterval($k))->format('F j, Y')}})</option>
                                @else
                                    <option value="{{$k}}">{{$option}} (Restart on {{$user->subscription->ends_at->add(new DateInterval($k))->format('F j, Y')}})</option>
                                @endif
                            @endforeach
                        </select>
                        <br>
                        <button class="btn btn-primary">Pause Account</button>
                        <a class="btn btn-default"href="{{route("billing")}}">Nevermind</a>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section("scripts")

@endsection