@extends("layouts/home")


@section("content")
    <div class="outer-vertical">
        <div class="middle-vertical">
            <div class="inner-vertical">

                <div class="form-container">

                    @if(session('message_info'))
                        <div class="bs-callout bs-callout-info">
                            <h4>Info</h4>
                            {{session('message_info')}}
                        </div>
                    @endif

                    @if($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="bs-callout bs-callout-danger">
                                <h4>Error</h4>
                                {{$error}}
                            </div>
                        @endforeach
                    @endif

                    <div class="form-title">
                        <h1>{{ __('Login to Buscatupaz') }}</h1>
                    </div>

                    <form action="{{route("post_login")}}" method="post">

                        {{ csrf_field() }}
                        <div class="form-group">
                            <input class="form-control" name="email" type="text" placeholder="Email" value="{{ old('email') }}"/>
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="password" type="password" placeholder="{{__('Password')}}"/>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-primary btn-block" type="submit">Login</button>
                        </div>

                        <div class="container-fluid login-actions">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <a class="login-link" href="{{route("password_reset")}}">{{ __('Forgot Password') }}?</a>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <a class="login-link" href="{{route("usersignup")}}">{{ __('Create an Account') }}</a>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>


@endsection
