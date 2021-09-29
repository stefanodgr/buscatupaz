@extends("layouts.main")

@section("content")

    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
            </div>
            <div class="breadcrumb-actions">
                <div class="breadcrumb-actions-wrapper">
                </div>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="teachers">
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

        <div id="teachers-container"></div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            function loadStudents(){
                $("#teachers-container").load("{{route("get_students")}}",function(){

                });
            }

            loadStudents();

        })
    </script>
@endsection