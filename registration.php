<?php

require "common.php";
checkLogIn();

if(!isAdministrator()) {
  echo "<h1>no tienes permiso para usar esta pagina</h1>";
  die();
}

require "templates/header.php";

if(isset($_GET['userAdded'])) {
  displayActionStatus('userAdded', 'Usuario agregado con exito!');
}

try {

  $sql = "SELECT * FROM programs";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsPrograms = $statement->fetchAll();

} catch(PDOException $error) {

  handleError($error);
}

?>

<form id="registration-form" method="post" action="actions/addUser.php">
	<label for="firstName">Nombre:</label>
	<input type="text" name="firstName" id="firstName" required><br>
  <label for="lastname">Apellido:</label>
	<input type="text" name="lastName" id="lastName" required><br>
  <label for="username">Username:</label>
  <input type="text" name="username" id="username" required><br>
  <label for="password">Contraseña (no la olvides!):</label>
  <input type="password" name="password" id="password" required><br>
  <label for="password-repeat">Repite Contraseña:</label>
  <input type="password" id="password-repeat" required><span id="password-warning" style="color: red" hidden>  Contraseñas no son iguales</span><br>
  <label for="isAdministrator" class="administrator-info">es Administrador(a)?:</label>
  <input type="checkbox" name="isAdministrator" id="isAdministrator" class="administrator-info" value="administrator"/><br>
	<label for="isCoordinator" class="coordinator-info">es Coordinador(a) de Programas?:</label>
  <input type="checkbox" name="isCoordinator" id="isCoordinator" value="coordinator" class="coordinator-info"/><br>
  <label for="programs" class="coordinator-info program-list" hidden>a cuales programas tendrá acceso?:</label>
  <?php foreach($resultsPrograms as $row) {
    $programId = $row["programId"];
    $programName = $row["name"];?>
  <label class="coordinator-info program-list" for="program-<?php echo $programId;?>" hidden><?php echo $programName;?></label>
  <input type="checkbox" id="program-<?php echo $programId;?>" name="program-<?php echo $programId;?>" class="coordinator-info program-list" value="program-<?php echo $programId;?>" hidden>
  <?php } ?>
  <label for="isTeacher">es Maestr@?:</label>
  <input type="checkbox" name="isTeacher" id="isTeacher" value="teacher"><br>
  <label for="courses" class="teacher-info" hidden>a cuales cursos tendrá acceso?</label>
	<input type="submit" name="submit" value="Submit">
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/registration.js"></script>


<?php require "templates/footer.php"; ?>
