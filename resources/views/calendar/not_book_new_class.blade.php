@extends("layouts.main")

@section("content")

    <br><br><br><h1>{{ __('Book New Class') }}</h1>

	<div class="not-book-class">
	    <p class="text-not-book-class">You will have access to unlimited one-on-one tutoring online for the duration of your GrammarLess program. This is intended as conversation practice only, and we’d recommend to not try to learn anything new, but simply practice what you’re learning in your in-person classes.</p>

		<br><p class="text-not-book-class">Your GrammarLess program has not begun, so you can’t book classes yet.</p>
	</div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {

        })
    </script>
@endsection