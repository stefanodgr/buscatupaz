<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>{{__('Hi')}} {{ $user->first_name }},</p>

<p>{{ __('Your next classes are confirmed') }}:</p>


@foreach ($classes_for_student as $class)
        <p>{{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("l d")}} {{__('DATE_AT')}} {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}, {{__('with')}} {{$class->teacher->first_name}}. Zoom username: {{$class->teacher->email}}</p>


        <p>{!!__('Your link for this meeting is')!!}:  {{$class->session_link}}.</p>        
@endforeach

{{-- <p>Here's a <a href="https://baselang.com/support/how-to-use-zoom/">guide to using Zoom</a>. --}}

<p>{!!__('EMAIL_YOUR_QUESTIONS_DESC')!!}.</p>

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>


</body>
</html>