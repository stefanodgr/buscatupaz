<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hello {{ $user->first_name }},</p>

		<p>You are receiving this notification because you have (or someone pretending to be you has) requested a password reset on your account on BaseLang. If you did not request this notification then please ignore it, if you keep receiving it please contact support.</p>

		<p>Please visit the following link in order to reset your password:</p>

		<p><a href="{{ $resetLink  }}">{{ $resetLink }}</a></p>

		<p>Buscatupaz</p>
	</body>
</html>
