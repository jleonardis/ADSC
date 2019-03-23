<?php

  require "../common.php";

  checkLogIn();
  if(!hasAdminPermission()) {
    echo $invalidPermissionMessage;
    die();
  }

try {

  if(isAdministrator()) {
    $sql = "SELECT programId, name FROM programs;";
    $statement = $connection->prepare($sql);
  }
  else if(isCoordinator()) {
    $sql = "SELECT programId, name FROM programs WHERE programId IN (
        SELECT programId
        FROM programCoordinators
        WHERE coordinatorId = :participantId
      );";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':participantId', $_SESSION['participantId'], PDO::PARAM_INT);
  }

  $statement->execute();
  $programs = $statement->fetchAll();
}
catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";

?>
<main>
  <div class="form-parent">
  <form method="post" action="/actions/addDivision.php" class="submit-form">
    <h2>Agregar División</h2>
    <label for="name">Nombre de Eje: </label>
    <input type="text" id="name" name="name"><br>
    <label for="program">Programa: </label>
    <select id="program" name="program">
      <option value="">--Elige el Programa--</option>
      <?php foreach($programs as $program) { ?>
        <option value="<?php echo escape($program['programId']); ?>"><?php echo escape($program['name']); ?></option>
      <?php } ?>
    </select><br>
    <label for="description">Descripción: </label>
    <textarea id="description" name="description" maxlength="255"></textarea><br><br>
    <input id="submit" name="submit" type="submit" value="Agregar" class="orange-submit">
  </form>
</div>
</main>

<?php include "../templates/sidebar.php"; ?>
<?php include "../templates/footer.php"; ?>
