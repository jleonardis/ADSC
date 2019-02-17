<?php

require "../common.php";

checkLogin();

if(isset($_GET) && isset($_GET["courseId"])) {
  $courseId = $_GET['courseId'];
} else {
  echo "no hay curso seleccionado";
}

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

try {

  $sql = "SELECT par.participantId as participantId, firstName, lastName,
      name, description, grade, gradeId, a.assignmentId as assignmentId
    FROM
      (
        SELECT name, description, assignmentId
        FROM assignments
        WHERE courseId = :courseId
          AND alive
      ) a
    LEFT JOIN
      (
        SELECT p.participantId as participantId, firstName, lastName,
          grade, gradeId, assignmentId
        FROM currentParticipantCourses_View pc
        JOIN participants p
          ON p.participantId = pc.participantId
        JOIN grades g
          ON g.participantId = p.participantId
        WHERE pc.courseId = :courseId
      ) par
    ON par.assignmentId = a.assignmentId;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $hasAssignments = $statement->rowCount() !== 0;
  $assignmentsTable = array();
  $assignmentNames = array();
  $assignmentInfos = array();
  if($hasAssignments) {

    $results = $statement->fetchAll();
    $hasParticipants = !is_null($results[0]['participantId']);

    foreach($results as $row) {
      $participantId = $row['participantId'];
      $participantName = $row['firstName'] . " " . $row['lastName'];
      if(!isset($assignmentsTable[$participantId])) {
        $assignmentsTable[$participantId] = array();
        $assignmentsTable[$participantId]['participantName'] = $participantName;
        $assignmentsTable[$participantId]['assignments'] = array();
      }
      $name = $row['name'];
      $description = $row['description'];
      if(array_search($name, $assignmentNames) === false) {
        array_push($assignmentInfos, array(
          'name' => $name, 'description' => $description, 'assignmentId' => $row['assignmentId']));
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
  <div id="back-button"><img src="/images/back-icon.png"></div>
  <h1>Tareas</h1>
  <div class="scrollDiv assignments">
    <form id="main-form" method="post" action="/actions/updateGrades.php?courseId=<?php echo escape($courseId);?>">
      <?php if($hasAssignments) { ?>
      <div class="scrollTableWrapper">
        <table class="scrollTable">
          <thead>
            <tr>
              <?php if($hasParticipants) { ?>
              <th class="fixed-column"> </th>
              <?php } ?>
              <?php foreach($assignmentInfos as $name) { ?>
                <th title="<?php echo escape($name['description']); ?>"><span style="text-align: center;"><?php echo escape($name['name']); ?></span><br>
                <input type="submit" value="Editar" formaction="/actions/updateGrades.php?assignmentId=<?php echo escape($name['assignmentId']); ?>&courseId=<?php echo escape($courseId); ?>&editRedirect=1"
                   style="color: red;"></th>

              <?php } ?>
            </tr>
          </thead>
          <?php if($hasParticipants) { ?>
          <tbody>
            <?php foreach($assignmentsTable as $participantId => $participant) { ?>
              <tr>
                <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                <?php foreach($assignmentInfos as $assignmentName) {
                  $assignmentInfo = $participant['assignments'][$assignmentName['name']];?>
                  <td>
                    <input type="number" max="100" name="<?php echo escape($assignmentInfo['gradeId']); ?>" value ="<?php echo escape($assignmentInfo['grade']);?>"></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        <?php } ?>
        </table>
      </div>
      <input type="submit" class="orange-submit" value="Actualizar">
    <?php } else {
      echo "este curso no tiene ninguna tarea asignada";
    } ?>
    </form>
  </div>

  <div id="addAssignment">
    <h2>Agregar Tarea</h2>
    <form method="post" action="/actions/addAssignment.php?courseId=<?php echo escape($courseId);?>" class="submit-form">
      <label for="name">Nombre: </label>
      <input type="text" id="name" name="name"><br>
      <label for="description">Descripción: </label>
      <textarea id="description" name="description" maxlength="255"></textarea><br>
      <input type="submit" value="Agregar" class="orange-submit">
    </form>
  </div>
</main>

<?php include "../templates/sidebar.php"; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<?php include "../templates/footer.php"; ?>
