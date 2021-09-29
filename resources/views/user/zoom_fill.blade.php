@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("profile")}}">
                    Profile <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    Zoom
                </a>

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="profile">

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

        <div class="profile-container">
            <div class="profile-container-title">
                {{ __('Add your Zoom Account') }}
            </div>
            <div class="profile-container-desc">
                <p>
                    {{ __('Add_Zoom_Account_Description') }}
                </p>
                <p>
                    {!!  __('Add_Zoom_Account_Description_Link') !!}
                </p>

                <div class="zoom_fill_container">
                    <form method="post" action="{{route("save_profile_zoom_email")}}">
                        {{ csrf_field() }}
                        <input type="text" class="form-control" name="zoom_email" placeholder="Enter your Zoom Email"/>
                        <button class="btn-primary btn">Continue</button>
                    </form>
                </div>


            </div>
        </div>

    </div>



@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });



    </script>
@endsection