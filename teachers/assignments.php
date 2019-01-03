<?php

require "../common.php";

checkLogin();
if(!hasPermission()) {
  echo $invalidPermissionMessage;
}


if(isset($_GET) && isset($_GET["courseId"])) {
  $courseId = $_GET['courseId'];
} else {
  echo "no hay curso seleccionado";
}

try {

  $sql = "SELECT * FROM assignments a INNER JOIN grades g
    ON a.assignmentId = g.assignmentId INNER JOIN participants p
    ON g.participantId = p.participantId WHERE a.courseId = :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $hasAssignments = $statement->rowCount() != 0;
  $assignmentsTable = array();
  $assignmentNames = array();

  if($hasAssignments) {

    $results = $statement->fetchAll();

    foreach($results as $row) {
      $participantId = $row['participantId'];
      $participantName = $row['firstName'] . " " . $row['lastName'];
      if(!isset($assignmentsTable[$participantId])) {
        $assignmentsTable[$participantId] = array();
        $assignmentsTable[$participantId]['participantName'] = $participantName;
        $assignmentsTable[$participantId]['assignments'] = array();
      }
      $name = $row['name'];
      if(array_search($name, $assignmentNames) === false) {
        array_push($assignmentNames, $name);
      }
      $assignmentsTable[$participantId]['assignments'][$name] = array();
      $assignmentsTable[$participantId]['assignments'][$name]['grade'] = $row['grade'];
      $assignmentsTable[$participantId]['assignments'][$name]['gradeId'] = $row['gradeId'];
    }
  }

} catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";

?>

<main>
  <h1>Tareas</h1>
  <?php if($hasAssignments) { ?>
  <div class="scrollDiv assignments">
    <form method="post" action="/actions/updateAssignments.php?courseId=<?php echo escape($courseId);?>">
      <div class="scrollTableWrapper">
        <table class="scrollTable">
          <thead>
            <tr>
              <th class="fixed-column"> </th>
              <?php foreach($assignmentNames as $name) { ?>
                <th><?php echo escape($name); ?></th>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach($assignmentsTable as $participantId => $participant) { ?>
              <tr>
                <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                <?php foreach($assignmentNames as $assignmentName) {
                  $assignmentInfo = $participant['assignments'][$assignmentName];?>
                  <td>
                    <input type="number" name="<?php echo escape($assignmentInfo['gradeId']); ?>" value ="<?php echo escape($assignmentInfo['grade']);?>"></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <input type="submit" name="submit" id="submit" class="orange-submit" value="Actualizar">
    </form>
  </div>
<?php }
else { ?>
  <p>Este curso no tiene ninguna tarea asignada.</p>
<?php } ?>
  <div id="addAssignment">
    <h2>Agregar Tarea</h2>
    <form method="post" action="/actions/addAssignment.php?courseId=<?php echo escape($courseId);?>" class="submit-form">
      <label for="name">Nombre: </label>
      <input type="text" id="name" name="name"><br>
      <label for="description">Descripción: </label>
      <textarea id="description" name="description" maxlength="255"></textarea><br>
      <input type="submit" name="submit" id="submit" class="orange-submit">
    </form>
  </div>
</main>

<?php include "../templates/sidebar.php";
include "../templates/footer.php"; ?>
