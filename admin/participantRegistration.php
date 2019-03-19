<?php

require "../common.php";
require "../data/localVariables.php";

checkLogIn();
if(!hasAdminPermission()) {
  echo $invalidPermissionMessage;
  die();
}

try {
  $sql = "SELECT programId, name FROM programs;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsPrograms = $statement->fetchAll();

  $sql = "SELECT username FROM users;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsUsers = $statement->fetchAll();

  $sql = "SELECT participantId, firstName, lastName, nickname, dpi FROM participants;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsParticipants = $statement->fetchAll();

} catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";

if(isset($_GET['participantAdded'])) {
	displayActionStatus('participantAdded', 'Participante agregado con exito!');
}

?>
<main>
<div class="form-parent">
<form enctype="multipart/form-data" id="registration-form" class="submit-form" method="post" action="../actions/addParticipant.php">
  <h2>Agregar Participante</h2>
	<label for="firstName">Nombre: </label>
	<input class="names" type="text" name="firstName" id="firstName" required><br>
  <label for="lastname">Apellido: </label>
	<input class="names" type="text" name="lastName" id="lastName" required><br>
  <label for="nickname">Apodo: </label>
  <input class="names" type="text" id="nickname" name="nickname"><br>
  <?php if(isAdministrator()) { ?>
  <label for="noDPI">No tiene DPI Guatemalteco?: </label>
  <input type="checkbox" name="noDPI" id="noDPI"><br>
<?php } ?>
  <label for="dpi">DPI: </label>
  <input type="text" id="dpi" name="dpi"><br>
  <label for="gender" required>Genero: </label>
  <select id="gender" name="gender" required>
		<option value="">--Elige género--</option>
    <option value="M">Masculino</option>
    <option value="F">Femenino</option>
    <option value="O">Otro</option>
  </select><br>
  <label for="dob">Fecha de Nacimiento: </label>
  <input type="date" id="dob" name="dob" required><br>
  <label for="email">Email: </label>
  <input type="text" id="email" name="email"><br>
  <label for="phoneNumber">Numero de Teléfono: </label>
  <input type="text" id="phoneNumber" name="phoneNumber"><br>
  <label for="phoneNumber_2">Numero de Teléfono 2: </label>
  <input type="text" id="phoneNumber_2" name="phoneNumber_2"><br>
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
  <label for="maritalStatus">Estado Civil: </label>
  <select id="maritalStatus" name="maritalStatus">
    <option value="">--Elige Estado Civil--</option>
    <?php foreach($maritalStatuses as $status) { ?>
      <option value="<?php echo escape($status); ?>"><?php echo escape($status);?></option>
    <?php } ?>
  </select><br>
  <label for="educationLevel">Nivel de Educación: </label>
  <select id="educationLevel" name="educationLevel">
    <option value="">--Elige Nivel de Educación--</option>
    <?php foreach($educationLevels as $level) { ?>
      <option value="<?php echo escape($level); ?>"><?php echo escape($level);?></option>
    <?php } ?>
  </select><br>
  <label for="picture">Imagen: </label>
  <input type="hidden" name="MAX_FILE_SIZE" value="1000000" /><!-- Add max size on php side!! -->
  <input type="file" id="picture" name="picture" accept="image"><br>
  <?php if(isAdministrator()) { ?>
  <label for="isAdministrator" class="administrator-info">Administrador(a):</label>
  <input type="checkbox" name="isAdministrator" id="isAdministrator" class="administrator-info role-select" value="administrator"/><br>
	<label for="isCoordinator" class="coordinator-info">Coordinador(a) de Programa:</label>
  <input type="checkbox" name="isCoordinator" id="isCoordinator" value="coordinator" class="coordinator-info role-select"/><br>
  <div class="program-list" hidden>
    <label for="programs">De cuales programas:</label>
    <select id="programs" name = "programs[]" multiple>
      <?php foreach($resultsPrograms as $program) { ?>
        <option value="<?php echo escape($program['programId']);?>"><?php echo escape($program['name']);?></option>
      <?php } ?>
    </select>
</div>
<?php } ?>
  <label for="isTeacher">Maestr@:</label>
  <input type="checkbox" name="isTeacher" id="isTeacher" value="teacher" class="role-select"><br>
  <label for="isTechnician">Tecnic@:</label>
  <input type="checkbox" name="isTechnician" id="isTechnician" value="technician" class="role-select"><br>
  <div class="user-info" hidden>
    <label for="username" required>Usuario:</label>
    <input type="text" name="username" id="username"><br>
    <label for="password" required>Contraseña:</label>
    <input type="password" name="password" id="password" class="password-box"><br>
    <label for="password-repeat">Repite Contraseña:</label>
    <input type="password" id="password-repeat" class="password-box"><br>
    <span id="password-warning" style="color: red" hidden>  Contraseñas no coinciden</span><br>
  </div>
  <label for="comments">Comentarios: </label>
  <textarea id="comments" name="comments"></textarea>
	<input type="submit" id="submit" name="submit" value="Agregar" class="orange-submit">
</form>
</div>
</main>

<?php include "../templates/sidebar.php";?>
<script>var usernames = <?php echo json_encode($resultsUsers); ?>;</script> <!-- this will be used later by registration.js -->
<script>var names = <?php echo json_encode($resultsParticipants);?>;</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="/js/registration.js"></script>
<?php include "../templates/footer.php"; ?>
