@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("billing")}}">
                    Billing <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item" href="{{route("cancel")}}">
                    Cancel <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>

                <a class="breadcrumb-item">
                    {{$reason->option}}
                </a>

            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrumb)?"main-content-wrapper-breadcrumb":""}}" id="billing">
        <div class="billing-container billing-container-reason">
            <form action="{{route("cancel_confirm",['reason'=>$reason->option])}}" method="post">
                {{ csrf_field() }}
                <div class="billing-title">
                    {{$reason->title}}
                </div>
                <div class="cancel-desc">
                    @if($reason->youtube)
                        <iframe class="youtube-player" type="text/html" src="https://www.youtube.com/embed/{{$reason->youtube}}?rel=0&showinfo=0&autoplay=1&modestbranding=1" frameborder="0"></iframe>
                    @endif
                    @if($reason->description)
                        {!! $reason->description !!}
                    @endif
                    @if($reason->feedback)
                        <textarea name="feedback" placeholder="Write here..."></textarea>
                    @endif
                </div>
                <div class="cancel-actions">
                    @foreach($reason->pages as $k=>$page)
                        <a href="{{filter_var($k, FILTER_VALIDATE_URL)?$k:route($k)}}" class="btn btn-primary">{{$page}}</a>
                    @endforeach
                    <button type="submit" class="btn btn-outline btn-danger">I Still Want to Cancel</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {


        })
    </script>
@endsection