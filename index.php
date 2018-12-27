<?php
require "common.php";
checkLogIn();
header('location: courseList.php');
die();

include "templates/header.php";

?>
<main>
<h2>Bienvenid<?php if($_SESSION['gender'] == 'M') {
	echo "o ";
}
else if($_SESSION['gender'] == 'F') {
	echo "a ";
}
else {
	echo "@ ";
}
	echo $_SESSION['firstName']; ?>! Que quieres hacer?</h2>
<ul>
	<li><a href="courseList.php"><strong>Cursos</strong></a></li>
		<li><a href="participantList.php"><strong>Participantes</strong></a></li>
	<?php if(isAdministrator()) { ?>
		<li><a href="admin/participantRegistration.php"><strong>Agregar Participante</strong></a></li>
		<li><a href="admin/registration.php"><strong>Crear Cuenta</strong></a></li>
		<li><a href="admin/createProgram.php"><strong>Crear Programa</strong></a></li>
	<?php } ?>
		<li><a href="/actions/logout.php"><strong>Salir</strong></a></li>
</ul>
</main>

<?php include "templates/sidebar.php";
include "templates/footer.php"; ?>
