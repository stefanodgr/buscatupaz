<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hola Niall,</p>

		<p>We inform you that the second payment corresponding to the student immersion {{ $inmersion->student->email }} that starts on {{DateTime::createFromFormat("Y-m-d", $inmersion->inmersion_start)->format("F d, Y")}} could not be processed, we suggest you take action on this or contact the student.</p>
	    
	    <p>{{ __('The BaseLang Team') }}</p>
	</body>
</html>