<?php

require "../common.php";

include "../templates/header.php";

checkLogIn();

if(!isset($_GET['courseId']) || !isset($_GET['assignmentId'])) {
  echo "No hay tarea seleccionada";
  die();
}

$courseId = $_GET['courseId'];
$assignmentId = $_GET['assignmentId'];

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

try
{
  $sql = "SELECT name, description FROM assignments
  WHERE assignmentId = :assignmentId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() === 0) {
    echo "esa tarea ya no existe";
    die();
  }

  $assignment = $statement->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $error) {
  handleError($error);
  die();
}
 ?>
<main>
  <div class="form-parent">
    <form class="submit-form" method="post" action="/actions/updateAssignment.php?assignmentId=<?php echo escape($assignmentId); ?>&courseId=<?php echo escape($courseId); ?>">
      <h2>Editar Tarea</h2>
      <label for="name">Nombre: </label>
      <input type="text" name="name" id="name"
      value="<?php echo escape($assignment['name']); ?>"><br>
      <label for="description">Descripción: </label>
      <input type="text" name="description" id="description"
      value="<?php echo escape($assignment['description']);?>"><br>
      <input type="submit" value="Actualizar" class="orange-submit">
      <button id="removeAssignment" type="button" class="orange-submit" id="removeAssignment"
      data-href="/actions/removeAssignment.php?assignmentId=<?php echo escape($assignmentId) ?>&courseId=<?php echo escape($courseId); ?>">
      Eliminar</button>
    </form>
  </div>
</main>

<?php include "../templates/sidebar.php"; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/deleteButton.js"></script>
<?php include "../templates/footer.php"; ?>
