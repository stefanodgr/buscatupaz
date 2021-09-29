<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>{{ __('Hi') }} {{ $user->first_name }},</p>

<p>Este es un recordatorio de tus sesiones en buscatupaz.com.</p>

<p>{{ __('Your next classes are confirmed') }}:</p>

@foreach ($classes_for_teacher as $classes)
    @foreach ($classes as $class)
        <p>{{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("l d")}} {{__('DATE_AT')}} {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("h:ia")}}, {{__('with')}} {{$user->first_name}}. Zoom username: {{$user->zoom_email}}.</p>
        <p>{{__('FIRST_CLASS_PLEASE_ADD')}}.</p>
        <p></p>
    @endforeach
@endforeach


<p>Aquí hay una guía de <a href="https://www.youtube.com/watch?v=z3JYkDglvGQ">como usar Zoom</a>.</p>

<p>Si deseas cancelar una sesión porque no la puedes atender por favor entra a la siguiente dirección donde podrás <a href="https://portal.buscatupaz.com/teacher/classes">cancelar una sesión pendiente</a>:</p>

<p><a href="https://portal.buscatupaz.com/teacher/classes">Sesiones Programadas para tu Cuenta</a></p>


<p>Para preguntas o reportar cualquier problema por favor envíanos un email a <a href="mailto:info@buscatupaz.com">info@buscatupaz.com</a>.</p>

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>


</body>
</html>