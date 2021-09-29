<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>Hi {{ $teacher->first_name }},</p>

@foreach ($classes as $class)
    <p>You have a class on {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("l d")}} at {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("h:ia")}}, with {{$user->first_name}}. Their zoom username is {{$user->zoom_email}}.</p>
    <p>If this is your first class with this student, please add them as a contact as soon as possible.</p>
    <p></p>
@endforeach

<p>For any questions, please talk to your coordinator or Karina.</p>

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>

</body>
</html>