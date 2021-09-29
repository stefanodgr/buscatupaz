<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hola {{ $user->first_name }},</p>
		<p>{{ __('Thanks') }}< for booking your spot in BaseLang Immersion in {{ucfirst($location->name)}} for the following times:</p>

	    @foreach($selecteds as $key => $selected)
	        @php 
	            $sel=explode(",",$selected);
	        @endphp
	        <p>{{DateTime::createFromFormat("Y-m-d", $sel[0])->format("M d")}} - @if(DateTime::createFromFormat("Y-m-d", $sel[0])->format("m") != DateTime::createFromFormat("Y-m-d", $sel[1])->format("m")) {{DateTime::createFromFormat("Y-m-d", $sel[1])->format("M d")}}, @else {{DateTime::createFromFormat("Y-m-d", $sel[1])->format("d")}}, @endif {{DateTime::createFromFormat("Y-m-d", $sel[0])->format("Y")}}, @if($sel[2]=="AM") morning classes (8:30am to 12:30pm) @else afternoon classes (1:30pm to 5:30pm) @endif with {{$teacher->first_name}}</p>
	    @endforeach

	    @if($user->registered_inmersion)
			@if(isset($location) && $location->email_message)
	    		<p>{{$location->email_message}}</p>
			@endif
	    	<p>Email: {{ $user->email }}</p>
	    	<p>{{ __('Password') }}: {{ session("inmersion_password") }}</p>
	    @endif

		@if(isset($location) && $location->survey)
    		<p>To help us prepare for your arrival, please fill out <a href="{{$location->survey}}"> this short survey</a>.</p>
		@endif
	    <p>We're looking forward to seeing you. If you have any questions, just hit reply to this email.</p>
	    <p>¡Un abrazo!</p>
	    <p>{{ __('The BaseLang Team') }}</p>
	</body>
</html>