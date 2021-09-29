<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>New feedback of student: {{$user->first_name}} {{$user->last_name}} ({{$user->email}})</b></p>
		<br>
		<p>Feedback:</p>
		<p>{{$feedback->feedback}}</p>
	</body>
</html>