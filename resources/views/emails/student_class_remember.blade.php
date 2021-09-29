<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>Hi {{ $user->first_name }},</p>

<p>{{ __('Your next classes are confirmed') }}:</p>

<p>Just a reminder that you have a class with {{$class->teacher->first_name}} starting in 10 minutes.</p>
<p><i>Their zoom username is: {{$class->teacher->email}}</i></p>

<p>Here's a <a href="https://baselang.com/support/how-to-use-zoom/">guide to using Zoom</a>.</p>

<p>For any questions, please email <a href="mailto:support@baselang.com">support@baselang.com</a> and we’ll get back to you as quickly as possible.</p>

<p>{{ __('Thanks') }}<br>{{ __('BaseLang') }}</p>


</body>
</html>