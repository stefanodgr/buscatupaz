@extends("layouts.inmersion")

@section("content")

    <br>
    <div class="text-center">
        <img src="{{asset("img/booked.png")}}" title="booked icon"/>
        <h1>Your Spot Is Locked In!</h1>
        <div class="div-text-succes">
            @if(isset($inmersion->location_id) && $inmersion->location_id==1)
                <p class="text-inmersion inm-margin">We’re excited to see you in {{ucfirst($location->name)}} in {{DateTime::createFromFormat("Y-m-d", $inmersion->inmersion_start)->format("F")}}, {{$user->first_name}}.</p>
                <p class="text-inmersion inm-margin">Our school is located here, in the Laureles neighborhood.</p><br>

                <iframe style="border:0; width:100%; height: 200px;" src="https://www.google.com/maps/embed/v1/place?q=Cra.%2077%20%2339-40%2C%20Medell%C3%ADn%2C%20Antioquia%2C%20Colombia&key=AIzaSyDYWBtuBVoJhB8cyZtwy_EutUerug1OqM4" allowfullscreen></iframe>

                <p class="text-inmersion inm-margin">We’d recommend buying flights, and organizing your accommodation (near our school in the Laureles neighborhood) if you haven’t already. Click below to log in to your new BaseLang account and review the advice videos and information about the city</p>
            @endif
            <p class="text-inmersion inm-margin"><a href="{{route('dashboard_inmersion')}}">Click here to login to your BaseLang account.</a></p>

        </div>
    </div>
    <br><br><br>

@endsection