<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>Hi {{ $user->first_name }},</p>

<p>{{ __('Your next classes are confirmed') }}:</p>


@foreach($classes as $class)
    <p>{{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("l d")}} at {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}, with {{$class->teacher->first_name}}. Zoom username: {{$class->teacher->email}}</p>
@endforeach

<p>Here's a <a href="https://baselang.com/support/how-to-use-zoom/">guide to using Zoom</a>.

<p>For any questions, please email <a href="mailto:support@baselang.com">support@baselang.com</a> and we’ll get back to you as quickly as possible.</p>

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>


</body>
</html>