@extends("layouts/home")


@section("content")
    <div class="outer-vertical">
        <div class="middle-vertical">
            <div class="inner-vertical">

                <div class="form-container">

                    <div class="form-title">
                        <h1>{{ __('Forgot Password') }}?</h1>
                    </div>

                    <form action="{{route("post_password_reset")}}" method="post">

                        @if($errors->any())
                            <h4>{!! $errors->first() !!}</h4>
                        @endif

                        {{ csrf_field() }}
                        <div class="form-group">
                            <input class="form-control" name="email" type="text" placeholder="Email"/>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-primary btn-block" type="submit">{{ __('Send Reset Link') }}</button>
                        </div>

                        <div class="container-fluid login-actions">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">

                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <a class="login-link" href="{{route("login")}}">{{ __('Remember? Login') }}</a>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>


@endsection