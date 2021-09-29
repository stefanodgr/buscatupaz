<div class="container-fluid top-navigation">
    <div class="row top-row div-max-res">
        <div class="col-xs-12 col-sm-8 text-left">
            <div class="container menu-first-step">
                
                <img class="item-logo" src="{{asset("img/logo-menu.png?v=2")}}" alt="BuscatuPaz.com Logo"/>
                
                <a href="{{route("inmersion",["location"=>$location->name])}}" class="item-first-step-f item-one decorate">Pick Your Dates</a> 

                <b class="separator-first-step"><i class="fa fa-angle-right" aria-hidden="true"></i></b>

                <a class="item-first-step item-two decorate btn-to-second">Pick Your Teacher</a>

                <b class="separator-first-step"><i class="fa fa-angle-right" aria-hidden="true"></i></b> 

                <a class="item-first-step item-three decorate btn-to-third">Your {{ __('Basic Info') }}</a>

                <b class="separator-first-step"><i class="fa fa-angle-right" aria-hidden="true"></i></b> 

                <b class="item-first-step item-four item-step-active">Pay Deposit</b>

            </div>
        </div>
        <div class="col-xs-12 col-sm-4 text-right div-next">
            <button id="pay-dep" class="btn btn-primary btn-next-step pay-scroll">Pay ${{$price}} and Reserve Spot</button>
        </div>
    </div>
</div>

<div class="container-fluid menu-immersion-res">
    <div class="main-menu menu-inmersion">
        <div class="main-menu-header header-inmersion">
            <a>
                <img src="{{asset("img/logo-menu.png?v=2")}}" alt="BuscatuPaz.com Logo"/>
            </a>
            <a class="main-menu-responsive-bars">
                <img class="inactive-image" src="{{asset("img/menu-open.png")}}" />
                <img class="active-image" src="{{asset("img/menu-close.png")}}" />
            </a>
            <br>
            <div class="text-right btn-step-immersion">
                <button id="pay-dep" class="btn btn-primary btn-next-step btn-res pay-scroll">Pay ${{$price}}</button>
            </div>
        </div>
        @include("inmersion.includes.menu.immersion_four_step")
    </div>
</div>