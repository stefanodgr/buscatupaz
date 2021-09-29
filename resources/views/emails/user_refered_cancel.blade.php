<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>

            <p>Hi {{ $user->first_name }},</p>

            <p>Lo siento!</p>

            @if($cancel)
                <p>Your friend, {{$userRefered->first_name}} {{$userRefered->last_name}}, who signed up last week for BaseLang with your referral link cancelled before the end of their trial. This is pretty rare!</p>
            @else
                <p>Your friend, {{$userRefered->first_name}} {{$userRefered->last_name}}, who signed up last week for BaseLang with your referral link downgraded to an Hourly account before the end of their trial.</p>
            @endif



            <p>Thus, you didn't get any free time with BaseLang. Sorry about that.</p>

            <p>Remember - there are no limits on how many friends you can refer for free time, so if you have other friends that you think would love it as much as you do, please continue to share the word! Your referral link is: https://baselang.com/realworld/?referral={{urlencode($user->email)}}.</p>

            <p>If they are still with us in a week after the trial ends, you will get one free month of BaseLang. We'll send you an email confirmation when that happens, along with how that will work.</p>
            <p>Thank you so much for sharing BaseLang with your friends!</p>


            <br>
            <p>Saludos,</p>
            <p>Connor & the whole BaseLang Team</p>
	</body>
</html>