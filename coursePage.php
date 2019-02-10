<?php

require "common.php";
checkLogIn();

include "templates/header.php";

if(!isset($_GET['courseId'])) {
  echo "no hay curso seleccionado";
  die();
}

$courseId = $_GET['courseId'];

if(!hasPermission($courseId)) {
  echo "No tienes permiso para ver este curso";
  die();
}

displayActionStatus('participantsAdded', 'Participante(s) agregado con exito!');
displayActionStatus('attendanceUpdated', "asistencia actualizado con exito!");
displayActionStatus('assignmentsUpdated', 'tareas actualizadas con exito!');

//get course info
try {

  //NOTE: CONSIDER COMBINING THESE SELECT STATEMENTS

  $sql = "SELECT teacherId, name, description, daysOfWeek, startDate, endDate
  FROM courses WHERE courseId = :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() == 0) {
    echo "ese curso ya no existe";
    die();
  }

  $course = $statement->fetch(PDO::FETCH_ASSOC);

  if($course['teacherId']) {

    $sql = "SELECT participantId, firstName, lastName FROM participants
    WHERE participantId = :teacherId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':teacherId', $course['teacherId'], PDO::PARAM_INT);

    $statement->execute();
    $teacher = $statement->fetch(PDO::FETCH_ASSOC);

  }

  $sql = "SELECT p.participantId as participantId, firstName, lastName, gender
  FROM participants p JOIN currentParticipantCourses_View pc
  ON p.participantId = pc.participantId WHERE pc.courseId = :courseId;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $resultsCourseParticipants = $statement->fetchAll();


  $sql = "SELECT pc.participantId as participantId, attended
  FROM attendance a JOIN currentParticipantCourses_View pc
  ON a.participantId = pc.participantId JOIN courseSessions cs
  ON a.sessionId = cs.sessionId AND pc.courseId = cs.courseId
  WHERE cs.courseId = :courseId AND
  cs.sessionDate < NOW() AND cs.alive;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $attendanceScores = array();

  if($statement->rowCount() != 0) {
    $resultsAttendance = $statement->fetchAll();
    //used to calculate percentage attendance (calculated in the html)
    foreach($resultsAttendance as $row) {
      $parId = $row['participantId'];
      if(!isset($attendanceScores[$parId])){
        $attendanceScores[$parId] = array(
          'attended' => 0,
          'total' => 0);
      }
      if($row['attended'] === 'present') {
        $attendanceScores[$parId]['attended']++;
      }
      if($row['attended'] !== 'excused'){
        $attendanceScores[$parId]['total']++;
      }
    }
  }

  $sql = "SELECT p.participantId as participantId, grade
  FROM grades g JOIN assignments a
  ON a.assignmentId = g.assignmentId JOIN currentParticipantCourses_View p
  ON g.participantId = p.participantId AND p.courseId = a.courseId
  WHERE a.courseId = :courseId;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $assignmentsScores = array();

  if($statement->rowCount() != 0) {
    $resultsAssignments = $statement->fetchAll();
    //used to calculate percentage attendance (calculated in the html)
    foreach($resultsAssignments as $row) {
      $parId = $row['participantId'];
      if(!isset($assignmentsScores[$parId])){
        $assignmentsScores[$parId] = array(
          'score' => 0,
          'total' => 0);
      }
      if($row['grade']) {
        $assignmentsScores[$parId]['score'] += $row['grade'];
      }
      if($row['grade'] !== null){
        $assignmentsScores[$parId]['total'] += 100;
      }
    }
  }




} catch(PDOException $error) {
  handleError($error);
  die();
}

//get list of participants

if(hasAdminPermission() || isTechnician()) {
  try {
    $sql = "SELECT participantId, firstName, lastName FROM participants p
    WHERE NOT EXISTS (SELECT 1 FROM currentParticipantCourses_View pc
    WHERE pc.participantId = p.participantId AND pc.courseId = :courseId)";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    $resultsAllParticipants = $statement->fetchAll();
  } catch(PDOException $error) {
    handleError($error);
    die();
  }
}


?>
<main>
<div id="courseHeading" class="heading">
<div>
<h1><?php echo escape($course['name']); ?></h1>
<div><strong><?php echo escape($course['description']); ?></strong></div>
</div>
<div><p><?php echo escape($course['daysOfWeek']); ?><br><?php echo escape(date('d/m/Y', strtotime($course['startDate'])) . "   hasta   " . date('d/m/Y', strtotime($course['endDate']))); ?></p></div>
<?php if (isset($teacher)) { ?>
<a href="/participantPage.php?participantId=<?php echo escape($teacher['participantId']);?>"><strong><?php
  echo "Enseñado por " . escape($teacher['firstName'] . " " . $teacher['lastName']);?></strong></a>
<?php } ?>
</div>
<?php if (isCoordinator() || isAdministrator()) { ?>
  <form method="post" action="admin/editCourse.php?courseId=<?php echo escape($courseId);?>">
    <input type=submit id="editCourse" class="orange-submit edit-button" value="editar">
  </form>
<?php } ?>

<div id="courseInfo">
<h2>Participantes: </h2>
<?php if(count($resultsCourseParticipants) == 0) { ?>
  <p>Este curso no tiene participantes.</p>
<?php } else { ?>
<table id="participantList">
  <thead>
    <th>Nombre</th>
    <th>Apellido</th>
    <th>Género</th>
    <th>Asistencia</th>
    <th>Punteo</th>
    <th></th>
  </thead>
  <tbody>
  <?php foreach($resultsCourseParticipants as $participant) { ?>
    <tr class="participant-row" data-href="/participantPage.php?participantId=<?php echo escape($participant['participantId']);?>">
      <td><?php echo escape($participant['firstName']);?></td>
      <td><?php echo escape($participant['lastName']);?></td>
      <td><?php echo escape($participant['gender']);?></td>
      <td><?php if(count($attendanceScores) == 0 || $attendanceScores[$participant['participantId']]['total'] === 0) {
        echo "-";
      }
      else {
        echo escape(round((($attendanceScores[$participant['participantId']]['attended']/$attendanceScores[$participant['participantId']]['total']) * 100)) . "%");
      }?></td>
      <td><?php if(count($assignmentsScores) === 0 || $assignmentsScores[$participant['participantId']]['total'] === 0) {
        echo "-";
      }
      else {
        echo escape(round((($assignmentsScores[$participant['participantId']]['score']/$assignmentsScores[$participant['participantId']]['total']) * 100)) . "%");
      }?></td>
      <td class="remove-participant" style="color: red; text-transform: uppercase;"
      data-href="/actions/removeParticipantFromCourse.php?participantId=<?php echo escape($participant['participantId']); ?>&courseId=<?php echo escape($courseId);?>">Quitar</td>
    </tr>
  <?php } ?>
</tbody>
</table>
</div>
<?php } ?>

 <!-- FOR NOW I'M DOING THIS ON THE BROWSER. COULD BE SWITCHED TO AJAX LATER. DEPENDS ON INTERNET SPEED. -->
 <div id="courseActions">
  <div id="courseManagement">
    <h2> Acciones de Maestr<?php echo escape(getGenderEnding($_SESSION['gender']));?></h2>
    <div id="courseManagementButtons">
      <a href="/teachers/attendance.php?courseId=<?php echo escape($courseId);?>">
        <button type="button" id="attendanceButton" class="orange-submit">Actualizar Asistencia</button>
      </a>
      <a href="/teachers/assignments.php?courseId=<?php echo escape($courseId);?>">
        <button type="button" id="assignmentsButton" class="orange-submit">Ver Tareas</button>
      </a>
      <a href="/teachers/quotas.php?courseId=<?php echo escape($courseId); ?>">
        <button type="button" id="quotasButton" class="orange-submit">Ver Quotas</button>
      </a>
    </div>
  </div>
  <?php if (hasAdminPermission() || isTechnician()){ ?>
  <div id="addParticipants">
  <h2>Agregar Participantes</h2>
 <input class="orange-search" type="text" id="searchBox">
 <button class="orange-submit" id="search">Buscar</button>
 <form method="post" action="actions/addParticipantsToCourse.php?courseId=<?php echo escape($courseId); ?>">
   <input class="orange-submit addParticipants" type="submit" name="submit" id="submit" value="Agregar Participantes" hidden>
    <table id="addParticipantTable" class="search-group">
        <thead>
          <!-- <tr class="search-head" hidden>
            <th>Nombre</th>
            <th>Seleccionar</th>
          </tr> -->
        </thead>
        <tbody>
          <?php foreach($resultsAllParticipants as $participant) {?>
            <tr class="search-row" hidden>
              <td class="table-cell"><?php echo escape($participant['firstName'] . " " . $participant['lastName']);?></td>
              <td class="table-cell"><input type="checkbox" value="check" class="select-checkbox" name="<?php echo escape($participant['participantId']); ?>"></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </form>
 </div>
<?php } ?>
</div>
</main>

<?php include "templates/sidebar.php"; ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/search.js"></script>

<?php require "templates/footer.php"; ?>
