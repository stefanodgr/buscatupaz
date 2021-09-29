<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<p>Hi {{ $user->first_name }},</p>

<p>While we’ve locked down all of your selected slots to be prebooked going forward, one or more of your selected times had already been booked by someone else for this week (as it was available for normal booking). Please double check your <b>{{ __('Scheduled Classes') }}</b>.</p>

<p>{{ __('BaseLang') }}</p>

</body>
</html>