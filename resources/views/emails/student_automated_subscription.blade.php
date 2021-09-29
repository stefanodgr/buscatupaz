<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hi {{$user->first_name}},</p>

		<p>Your BaseLang account is currently paused, as requested. The pause will end in three days on {{DateTime::createFromFormat("Y-m-d",$date)->format("F j, Y")}}. If you'd still like to restart on that day, you're good to go! You don't need to do anything.</p>
		
		<p>Alternatively, you can:</p>
		<ul>
			<li><a href="{{route("restart_subscription_now",["token"=>$token])}}">Restart Now</a>.</li>
			<li><a href="{{route("restart_subscription_after",["token"=>$token])}}">Extend pause for 2 weeks</a>.</li>
			<li><a href="{{route("restart_subscription_after",["token"=>$token,"month"=>true])}}">Extend pause for one month</a>.</li>
			<li><a href="{{route("stop_pause_subscription_token",["token"=>$token])}}">Fully Cancel</a>.</li>
		</ul>
		<p>If you'd like to do any of the above, just click the link.</p>

		<br>
		<p>The BaseLang team</p>

	</body>
</html>