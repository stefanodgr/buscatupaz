<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>{{ $user->first_name }},</p>

		<p>Bienvenido a <b>Buscatupaz.com!</b></p>

		<p>Aquí está tu información inicial de acceso:</p>
		<p>{{ $user->email }}</p>
		<p>12345</p>

		<p><a href="{{ route('home') }}">Click aquí</a> para ingresar y agenda tu primera sesión.</p>

		<br>
		<p>Saludos!</p>
		<p>El equipo de buscatupaz.com</p>

	</body>
</html>