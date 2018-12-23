<?php
require "common.php";
checkLogIn();

include "templates/header.php";

?>

<h2>Hola <?php echo $_SESSION['firstName']; ?>! Que quieres hacer?</h2>
<ul>
	<li><a href="courseList.php"><strong>Ver Cursos</strong></a></li>
		<li><a href="participantList.php"><strong>Ver Participantes</strong></a></li>
	<?php if(isAdministrator()) { ?>
		<li><a href="participantRegistration.php"><strong>Crear un Perfil de Participante Nuevo</strong></a></li>
		<li><a href="registration.php"><strong>Crear una Cuenta de Usuario Nueva</strong></a></li>
	<?php } ?>
</ul>

<?php include "templates/footer.php"; ?>
