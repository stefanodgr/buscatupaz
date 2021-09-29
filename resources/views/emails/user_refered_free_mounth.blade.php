<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>

            <p>Hi, {{ $user->first_name }},</p>

            <p>Congrats! Your friend, {{$userRefered->first_name}} {{$userRefered->last_name}}, who signed up last week for BaseLang with your referral link just finished their trial, and thus, you've been given one free month!.</p>
            <p>You don't need to do anything.</p>

            @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan=="baselang_hourly")
                <p>We have saved any credits you may have already purchased, and switched you to an unlimited account for the next 30 days, which means you can take as many classes as you want! After the 30 days are up, you will be switched back to your normal Hourly account.</p>
            @else
                <p>You'll notice that your next billing date has been pushed back 30 days in the billing page of the platform.</p>
            @endif

            <p>Remember - there are no limits on how many friends you can refer for free time, so if you have other friends that you think would love it as much as you do, please continue to share the word! Your referral link is: https://baselang.com/realworld/?referral={{urlencode($user->email)}}.</p>

            <p>If they are still with us in a week after the trial ends, you will get one free month of BaseLang. We'll send you an email confirmation when that happens, along with how that will work.</p>
            <p>Thank you so much for sharing BaseLang with your friends!</p>


            <br>
            <p>Gracias!</p>
            <p>Connor & the whole BaseLang Team</p>
	</body>
</html>