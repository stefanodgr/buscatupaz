<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hi!,</p>
		<p>The student {{$user->first_name}} {{$user->last_name}} - ({{$user->email}}) has acquired "{{$subscription}}".</p>

	    <p>{{ __('The BaseLang Team') }}</p>
	</body>
</html>