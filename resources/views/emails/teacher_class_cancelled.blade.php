<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>Hi {{ $teacher->first_name }},</p>

<p>Your class on date {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("l d")}} at {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($teacher->timezone))->format("h:ia")}}, with {{$user->first_name}} was cancelled.</p>

<p>They will no longer appear your dashboard, and that time slot is available to other students to book now.</p>

<p>For any questions, please talk to your coordinator or Karina.</p>

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>

</body>
</html>