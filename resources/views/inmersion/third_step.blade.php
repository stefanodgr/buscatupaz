@extends("layouts.inmersion")

@section("content")
    <h1 class="text-left title-pick margin-title">Login or Create Account</h1>
    <p class="text-inmersion inm-margin">Already have a BaseLang account? You can log in below. If not, you’ll need to create one at this step - just your name, email, and a password.</p>

    <br><br>
    <div class="container-login-create">
        <div class="container internal-second-step">
            
            <h3><b>Login</b></h3>
            <p class="text-inmersion">If you already have a BaseLang account, login here.</p>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <p class="text-inmersion">Email</p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <input type="email" class="form-control" id="email"/>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <p class="text-inmersion">{{ __('Password') }}</p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <input type="password" class="form-control" id="password"/>
                </div>
            </div>  

            <form id="post-calendar-login" action="{{route('pay_deposit')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="location_id" value="{{$location->id}}">
                @foreach($selecteds as $selected)
                    <input type="hidden" value="{{$selected}}" name="selecteds[]"/>
                @endforeach
            </form>
            
            @if($current_user)
                <br>
                <div class="row">
                    <div class="col-xs-12 col-sm-3">
                        
                    </div>
                    <div class="col-xs-12 col-sm-6 text-center">
                        <p class="text-inmersion">Do you want to continue as {{$current_user->first_name}}?</p>
                        
                        <form id="pay-user-logged" action="{{route('pay_deposit')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="location_id" value="{{$location->id}}">
                            @foreach($selecteds as $selected)
                                <input type="hidden" value="{{$selected}}" name="selecteds[]"/>
                            @endforeach
                            <button type="submit" class="btn btn-primary user-logged">Click Here!</button>
                        </form>
                        <br>
                    </div>
                </div>
            @endif

            <br><hr class="hr-second-step" /><br>

            <h3><b>Create Account</b></h3>
            <p class="text-inmersion">Not already a member? Create an account here. This is so you have access to our online platform to schedule extra conversation classes, watch videos about how to get the most out of your program, and read information about {{ucfirst($location->name)}}.</p>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <p class="text-inmersion">{{ __('First Name') }}</p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <input type="text" class="form-control" id="first_name" @if($current_user) value="{{$current_user->first_name}}" @endif/>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <p class="text-inmersion">{{ __('Last Name') }}</p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <input type="text" class="form-control" id="last_name" @if($current_user) value="{{$current_user->last_name}}" @endif/>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <p class="text-inmersion">Email</p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <input type="email" class="form-control" id="create_email" @if($current_user) value="{{$current_user->email}}" @endif/>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-3">
                    <p class="text-inmersion">{{ __('Password') }}</p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <input type="password" class="form-control" id="create_password"/>
                </div>
            </div><br><br>

        </div>
    </div><br><br>

    <button class="btn btn-primary btn-next-step">{{ __('Next Step') }}</button>
    <br><br><br><br><br><br>

    <form id="back-to-second" action="{{route('pick_your_teacher')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="location_id" value="{{$location->id}}">
        <input type="hidden" value="{{$selected}}" name="selecteds[]"/>
    </form>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $(".btn-next-step").click(function () {

                //Verify login
                var email = $("#email").val();
                var password = $("#password").val();

                if(email!='' && password=='') {
                    alert("Enter the password to login!");
                }else if(email=='' && password!='') {
                    alert("Enter the email to login!");
                }

                if(email!='' && password!='') {
                    $.post("{{route("inmersion_login")}}", {
                        "_token": "{{csrf_token()}}",
                        "email": email,
                        "password": password,
                    }, function (data) {
                        if(data.response=="not_activated") {
                            alert("User not activated!");
                        }else if(data.response=="redirect_pay_deposit") {
                            $(".btn-next-step").prop('disabled', true);
                            $("#post-calendar-login").submit();
                        }else if(data.response=="email_incorrect") {
                            alert("The email you entered is incorrect!");
                        }else if(data.response=="password_incorrect") {
                            alert("The password you entered is incorrect!");
                        }
                    });
                }

                //Verify create account
                var first_name = $("#first_name").val();
                var last_name = $("#last_name").val();
                var create_email = $("#create_email").val();
                var create_password = $("#create_password").val();

                if(first_name!='' && (last_name=='' || create_email=='' || create_password=='')) {
                    alert("Enter the missing fields to register the account!");
                }else if(last_name!='' && (first_name=='' || create_email=='' || create_password=='')) {
                    alert("Enter the missing fields to register the account!");
                }else if(create_email!='' && (first_name=='' || last_name=='' || create_password=='')) {
                    alert("Enter the missing fields to register the account!");
                }else if(create_password!='' && (first_name=='' || last_name=='' || create_email=='')) {
                    alert("Enter the missing fields to register the account!");
                }

                if(email=='' && password=='' && first_name!='' && last_name!='' && create_email!='' && create_password!='') {

                    $.post("{{route("inmersion_create_account")}}", {
                        "_token": "{{csrf_token()}}",
                        "first_name": first_name,
                        "last_name": last_name,
                        "email": create_email,
                        "password": create_password,
                        "location_id": '{{$location->id}}',
                    }, function (data) {
                        if(data.response=="existing_user") {
                            alert("This email ("+create_email+") is already registered!");
                        }else if(data.response=="short_password") {
                            alert("Passwords must be at least 5 characters");
                        }else if(data.response=="created_user") {
                            $(".btn-next-step").prop('disabled', true);
                            $("#post-calendar-login").append('<input type="hidden" value="'+data.user.id+'" name="user_id"/>');
                            $("#post-calendar-login").submit();
                        }else if(data.response=="update_password") {
                            alert("Password successfully updated!");
                            $("#post-calendar-login").submit();
                        }
                    });
                }

            });

            $(".user-logged").click(function () {
                $("#pay-user-logged").submit();
                $(this).prop('disabled', true);
            });


            $(".btn-to-second").click(function () {
                $("#back-to-second").submit();
            });

            $(".main-menu-responsive-bars").click(function () {
                $(this).toggleClass("active");
                $("#menu,.main-menu").toggleClass("active");
            });
            
        })
    </script>
@endsection