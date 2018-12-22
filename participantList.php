<?php
require "common.php";

checkLogIn();
if(!checkPermission()) {
	echo "No tienes permiso para usar esta pagina";
	die();
}

require "templates/header.php";


?>

<form id="participant-form" method="post" action="actions/addParticipant.php">
	<label for="firstName">Nombre: </label>
	<input type="text" name="firstName" id="firstName" required><br>
  <label for="lastname">Apellido: </label>
	<input type="text" name="lastName" id="lastName" required><br>
  <label for="gender" required>Genero: </label>
  <select id="gender" name="gender" required>
		<option value="">--Elige genero--</option>
    <option value="M">Masculino</option>
    <option value="F">Femenino</option>
    <option value="O">Otro</option>
  </select><br>
	<input type="submit" id="submit" name="submit" value="Submit">
</form>

<?php require "templates/footer.php"; ?>
