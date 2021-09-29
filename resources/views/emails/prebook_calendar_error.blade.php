<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hi Niall,</p>

		@foreach($prebooks as $class)
			<p>
				We inform you that the user {{$current_user->first_name}} {{$current_user->last_name}} ({{$current_user->email}}) edited the calendar of teacher {{$class->teacher->first_name}} {{$class->teacher->last_name}} ({{$class->teacher->email}}), affecting the classes of the student {{$class->student->first_name}} {{$class->student->last_name}} ({{$class->student->email}}), which is at {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->format('Y-m-d H:i:s')}} UTC, take forecasts.
			</p>
		@endforeach

		<p>The BaseLang team</p>

	</body>
</html>