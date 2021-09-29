<div id="menu">
    <div class="menu-section">
        <div class="menu-item">
            <a href="{{route("inmersion",["location"=>$location->name])}}" class="immersion-item decorate">
                Pick Your Dates
            </a>
        </div>
    </div>
    <div class="menu-section">
        <div class="menu-item">
            <a class="immersion-item decorate btn-to-second">
                Pick Your Teacher
            </a>
        </div>
    </div>
    <div class="menu-section">
        <div class="menu-item">
            <a class="immersion-item decorate btn-to-third">
                Your {{ __('Basic Info') }}
            </a>
        </div>
    </div>
    <div class="menu-section">
        <div class="menu-item">
            <a> 
                Pay Deposit
            </a>
        </div>
    </div>
</div>