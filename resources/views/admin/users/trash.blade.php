@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_users")}}">
                    Users <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    {{ __('Delete') }} <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_users")}}" class="btn btn-default">Cancel</a>
            </div>
        </div>

        <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="trash_action">

            @if($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="bs-callout bs-callout-danger">
                        <h4>Error</h4>
                        {!! $error !!}
                    </div>
                @endforeach
            @endif


            @if (session('message_info'))
                <div class="bs-callout bs-callout-info">
                    <h4>Info</h4>
                    {{ session('message_info') }}
                </div>
            @endif

            <h1>Delete User</h1>

            <div>
                <p>Are you sure you want to delete the user {{$edit_user->first_name}} {{$edit_user->last_name}} ({{$edit_user->email}})?</p>
                <p>Write DELETE to continue</p>
            </div>

            <form action="{{route("admin_users_delete")}}" method="post" >
                {{ csrf_field() }}
                <input type="hidden" name="user_id" value="{{$edit_user->id}}"/>

                <input class="form-control" type="text" value="" placeholder="{{ __('Delete') }}" id="confirm_delete">
                <button class="btn btn-danger" id="delete_button" disabled>Continue</button>
                <a class="btn btn-default" href="{{route("admin_users")}}">Cancel</a>
            </form>
        </div>

    @endif
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            $("#confirm_delete").keyup(function () {
                if($(this).val().toLowerCase()=="delete"){
                    $("#delete_button").prop("disabled",false);
                } else {
                    $("#delete_button").prop("disabled",true);
                }
            })
        });
    </script>
@endsection