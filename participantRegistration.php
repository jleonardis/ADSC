<?php

require "common.php";
checkLogIn();
if(!isAdministrator()) {
  header("location: index.php");
  die();
}

include "templates/header.php";

if(isset($_GET['participantAdded'])) {
	displayActionStatus('participantAdded', 'Participante agregado con exito!');
}

?>

<form enctype="multipart/form-data" id="participant-form" method="post" action="actions/addParticipant.php">
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
  <label for="picture">Imagen: </label>
  <input type="hidden" name="MAX_FILE_SIZE" value="1000000" /><!-- Add max size on php side!! -->
  <input type="file" id="picture" name="picture" accept="image">
	<input type="submit" id="submit" name="submit" value="Submit">
</form>

<?php include "templates/footer.php"; ?>
