<?php
require "common.php";
checkLogIn();

include "templates/header.php";

?>


<ul>
	<li><a href="courseList.php"><strong>Buscar Cursos</strong></li>
	<li><a href="registration.php"><strong>Agregar Usuario</strong></li>
	<li><a href="participantList.php"><strong>Agregar Participantes</strong></li>
</ul>

<?php include "templates/footer.php"; ?>
