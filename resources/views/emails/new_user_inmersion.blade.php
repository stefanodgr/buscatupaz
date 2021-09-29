<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hi!,</p>
		<p>The student {{$user->first_name}} {{$user->last_name}} has acquired BaseLang Immersion for the following date:</p>

	    @foreach($selecteds as $key => $selected)
	        @php 
	            $sel=explode(",",$selected);
	        @endphp
	        <p>{{DateTime::createFromFormat("Y-m-d", $sel[0])->format("M d")}} - @if(DateTime::createFromFormat("Y-m-d", $sel[0])->format("m") != DateTime::createFromFormat("Y-m-d", $sel[1])->format("m")) {{DateTime::createFromFormat("Y-m-d", $sel[1])->format("M d")}}, @else {{DateTime::createFromFormat("Y-m-d", $sel[1])->format("d")}}, @endif {{DateTime::createFromFormat("Y-m-d", $sel[0])->format("Y")}}, @if($sel[2]=="AM") morning classes (8:30am to 12:30pm) @else afternoon classes (1:30pm to 5:30pm) @endif with {{$teacher->first_name}}</p>
	    @endforeach

	    <p>{{ __('The BaseLang Team') }}</p>
	</body>
</html>