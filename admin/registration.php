<?php

require "../common.php";
checkLogIn();

if(!isAdministrator()) {
  echo "<h1>no tienes permiso para usar esta pagina</h1>";
  die();
}

require "../templates/header.php";

if(isset($_GET['userAdded'])) {
  displayActionStatus('userAdded', 'Usuario agregado con exito!');
}

try {

  $sql = "SELECT * FROM programs;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsPrograms = $statement->fetchAll();

  $sql = "SELECT username FROM users;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsUsers = $statement->fetchAll();

} catch(PDOException $error) {

  handleError($error);
}

?>
<main>
<div class="form-parent">
<form id="registration-form" class= "submit-form" method="post" action="../actions/addUser.php">
  <h2>Agregar Usuario</h2>
	<label for="firstName">Nombre:</label>
	<input type="text" name="firstName" id="firstName" required><br>
  <label for="lastname">Apellido:</label>
	<input type="text" name="lastName" id="lastName" required><br>
  <label for="username">Usuario:</label>
  <input type="text" name="username" id="username" required><br>
  <label for="password">Contraseña:</label>
  <input type="password" name="password" id="password" required><br>
  <label for="password-repeat">Repite Contraseña:</label>
  <input type="password" id="password-repeat" required><span id="password-warning" style="color: red" hidden>  Contraseñas no coinciden</span><br>
  <label for="gender">Género: </label>
  <select id="gender" name="gender" required>
		<option value="">--Elige género--</option>
    <option value="M">Masculino</option>
    <option value="F">Femenino</option>
    <option value="O">Otro</option>
  </select><br>
  <label for="isAdministrator" class="administrator-info">Administrador(a):</label>
  <input type="checkbox" name="isAdministrator" id="isAdministrator" class="administrator-info" value="administrator"/><br>
	<label for="isCoordinator" class="coordinator-info">Coordinador(a) de Programa:</label>
  <input type="checkbox" name="isCoordinator" id="isCoordinator" value="coordinator" class="coordinator-info"/><br>
  <label for="isTeacher">Maestr@:</label>
  <input type="checkbox" name="isTeacher" id="isTeacher" value="teacher"><br>
	<input type="submit" name="submit" value="Agregar" class="orange-submit">
</form>
</div>
</main>
<?php include "../templates/sidebar.php"; ?>
<script>var usernames = <?php echo json_encode($resultsUsers); ?>;</script> <!-- this will be used later by registration.js -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="../js/registration.js"></script>


<?php require "../templates/footer.php"; ?>
