@extends("layouts.main")

@section("content")

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="calendar">

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


        <div class="calendar-container">
            
            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    Prebook
                </div>
                <div class="calendar-container-desc" id="text-content-primary-pre">
                    <p class="text-content-prebook">With prebook, you can schedule with any teacher ahead of time.</p>
                    <p>For instance, if you prebook 9am on Monday with Carlos, that class will automatically be booked for you before the schedule is released. So not only do you get the teacher you want at the time you want, but you don’t have to manually book the class.</p><br>
                </div>
                <div class="content-dashboard-actions">
                    <a href="{{route("get_prebook")}}" class="btn btn-primary btn-block btn-pre">Learn More About Prebook</a>
                </div>
            </div>

            <br>

            <div class="book-class content-dashboard">
                <div class="content-dashboard-title">
                    Check Prebook Availability 
                </div>
                <div class="content-dashboard-desc" id="content-text-info">
                    <p class="text-content-prebook">We only allow 25% of a teacher’s time to be prebooked each day. This ensures all teachers are still available to all students, not just those who have paid for Prebook.</p>
                    <p>If you’d like to check if your favorite teachers have time available before buying prebook, click below to see their current schedules.</p>
                </div>
                <div class="content-dashboard-actions">
                    <a href="{{route("get_prebook_availability")}}" class="btn btn-primary btn-block btn-pre">Preview Prebook Availability</a>
                </div>
            </div>

        </div>

    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

        });
    </script>
@endsection