<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>{{__('Hi')}} {{ $teacher->first_name }},</p>

<p>{{ __('Your next classes are confirmed') }}:</p>

@foreach ($classes_for_teacher as $classes)
    @foreach ($classes as $class)
        <p>{{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("l d")}} {{__('DATE_AT')}} {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("h:ia")}}, {{__('with')}} {{$user->first_name}}. Zoom username: {{$user->zoom_email}}.</p>
        <p>{{__('FIRST_CLASS_PLEASE_ADD')}}.</p>
        <p></p>
    @endforeach
@endforeach

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>

</body>
</html>