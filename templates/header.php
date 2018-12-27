<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Base de Datos</title>

	<!--BE CAREFUL WITH RELATIVE STYLE.CSS PATH -->
	<link rel="stylesheet" type="text/css" href="/style.css">
	<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
	
</head>

<body>
	<header>
		<img id="logo" src="/images/AMIGOSLOGO.JPG"/>
		<h1>Base de Datos</h1>
		<?php $currentPage = $_SERVER['PHP_SELF'];
		if($currentPage != '/login.php' && $currentPage != '/index.php') { ?>
		<nav>
			<ul>
				<li><a href="/index.php">Inicio</a></li>
				<li><a href="/actions/logout.php">Salir</a></li>
			</ul>
		</nav>
	<?php } ?>
	</header>
