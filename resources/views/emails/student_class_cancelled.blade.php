<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>Hi {{ $user->first_name }},</p>

<p>Your class on date {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("l d")}} at {{DateTime::createFromFormat("Y-m-d H:i:s",$class->class_time)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}, with {{$class->teacher->first_name}} was successfully cancelled.</p>

<p>If you didn’t do this, please just go back into the platform to re-book the class.</p>

<p>{{ __('Thanks') }}< for cancelling ahead of time! This means other students who may have wanted the same time slot can now book it.</p>

<p>For any questions, please email support@baselang.com and we’ll get back to you as quickly as possible.</p>

<p>{{ __('BaseLang') }}</p>


</body>
</html>