<?php

require "../common.php";
require "../data/localVariables.php";

checkLogIn();
if(!isAdministrator()) {
  header("location: ../index.php");
  die();
}

include "../templates/header.php";

if(isset($_GET['participantAdded'])) {
	displayActionStatus('participantAdded', 'Participante agregado con exito!');
}

?>
<div class="form-parent">
<form enctype="multipart/form-data" id="participant-form" class="submit-form" method="post" action="../actions/addParticipant.php">
  <h2>Agregar Participante</h2>
	<label for="firstName">Nombre: </label>
	<input type="text" name="firstName" id="firstName" required><br>
  <label for="lastname">Apellido: </label>
	<input type="text" name="lastName" id="lastName" required><br>
  <label for="nickname">Apodo: </label>
  <input type="text" id="nickname" name="nickname"><br>
  <label for="gender" required>Genero: </label>
  <select id="gender" name="gender" required>
		<option value="">--Elige género--</option>
    <option value="M">Masculino</option>
    <option value="F">Femenino</option>
    <option value="O">Otro</option>
  </select><br>
  <label for="age">Edad: </label>
  <input type="number" id="age" name="age"><br>
  <label for="dob">Fecha de Nacimiento: </label>
  <input type="date" id="dob" name="dob"><br>
  <label for="village">Comunidad de Origen: </label>
  <select id="village" name="village">
    <option value="">--Elige aldea--</option>
    <?php foreach($towns as $town) { ?>
      <option value="<?php echo escape($town);?>"><?php echo escape($town);?></option>
    <?php } ?>
  <select><br>
    <label>Idiomas: </label><br>
    <?php foreach($languages as $language) { ?>
      <label for="language-<?php echo escape($language); ?>"><?php echo escape($language);?>: <input type="checkbox" id="language-<?php echo escape($language); ?>" name="language-<?php echo escape($language); ?>" value="<?php echo escape($language); ?>"></label>
    <?php } ?><br>
    <label for="language-other">Otros Idiomas: <input type="text" id="language-other" name="language-other"></label><br>
  <label for="picture">Imagen: </label>
  <input type="hidden" name="MAX_FILE_SIZE" value="1000000" /><!-- Add max size on php side!! -->
  <input type="file" id="picture" name="picture" accept="image"><br>
	<input type="submit" id="submit" name="submit" value="Agregar" class="orange-submit">
</form>
</div>

<?php include "../templates/footer.php"; ?>
