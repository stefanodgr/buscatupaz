@extends("layouts.home")

@section("content")
    <div class="outer-vertical">
        <div class="middle-vertical">
            <div class="inner-vertical">
                <div class="content-box">
                    <div class="color-box">
                        <br>
                        <div class="logo-platform">
                            <img src="{{asset("img/logo-menu.png?v=2")}}" alt="BuscatuPaz.com Logo"/>
                        </div>

                        <div class="info-fields">
                            <h3 class="centered-text">Oops, something has gone wrong, your session has expired or is invalid!</h3>

                            <div class="centered-text"><a class="info-link" href="{{route("home")}}">Go to the main page</a></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection