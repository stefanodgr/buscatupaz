<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hi {{$user->first_name}},</p>
		
		<p>Your @if(in_array($email_reminder->plan,["medellin_RW","medellin_RW_1199"])) Medellin RW @elseif($email_reminder->plan=="medellin_RW_Lite") Medellin RW Lite @else Medellin DELE @endif subscription will start on {{DateTime::createFromFormat("Y-m-d",$email_reminder->activation_day)->format("F d, Y")}}. Remember, all new Medellin students receive their first day as a trial for $1.</p>

		<p>From today, you can log in <a href="{{route("login")}}">here</a> and schedule in-person classes at our Medellin school for {{DateTime::createFromFormat("Y-m-d",$email_reminder->activation_day)->format("F d, Y")}}, and onwards.</p>

		<p>Our school is located here: https://goo.gl/maps/bh5J5RvXjnu</p>

		<p>If you’re not ready to begin yet, then you can change your scheduled start date by clicking <a href="{{route("billing")}}">here</a>.</p>

		<p>Un abrazo,</p>
		<p>{{ __('The BaseLang Team') }}</p>
	</body>
</html>
