@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item">
                    Feedback
                </a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="billing">

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

        <div class="billing-container">
            <div class="billing-title">
                {{ __('Leave your feedback') }}
            </div>
            <div class="cancel-desc">
                <p>
                    {{ __('Feedback Description') }}.
                </p>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <form action="{{route("save_feedback")}}" method="post">
                        {{csrf_field()}}
                        <textarea class="form-control" placeholder="{{ __('Leave your feedback') }}" name="feedback" required></textarea>
                        <br>
                        <button class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section("scripts")

@endsection