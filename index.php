<?php
require "common.php";
checkLogIn();

include "templates/header.php";

?>

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
	<?php } ?>
		<li><a href="/actions/logout.php"><strong>Salir<strong></a></li>
</ul>

<?php include "templates/footer.php"; ?>
