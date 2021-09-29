<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hi, {{ $userReferal->first_name }},</p>

		<p>Just wanted to let you know that your friend, {{$user->first_name}} {{$user->last_name}}, just signed up for BaseLang using your referral link.</p>

		<p>If they are still with us in a week after the trial ends, you will get one free month of BaseLang. We'll send you an email confirmation when that happens, along with how that will work.</p>
		<p>Thank you so much for sharing BaseLang with your friends!</p>


		<br>
		<p>Un abrazo,</p>
		<p>Connor & the whole BaseLang Team</p>

	</body>
</html>