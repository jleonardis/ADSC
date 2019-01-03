<?php

require "common.php";
checkLogIn();

include "templates/header.php";
if(!hasPermission()) {
  echo "No tienes permiso para ver este curso";
  die();
}

displayActionStatus('participantsAdded', 'Participante(s) agregado con exito!');
displayActionStatus('attendanceUpdated', "asistencia actualizado con exito!");


if(!isset($_GET['courseId'])) {
  echo "no hay curso seleccionado";
  die();
}

$courseId = $_GET['courseId'];

//get course info
try {

  $sql = "SELECT * FROM courses WHERE courseId = :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() == 0) {
    echo "ese curso ya no existe";
    die();
  }

  $course = $statement->fetch(PDO::FETCH_ASSOC);

  if($course['teacherId']) {

    $sql = "SELECT teacherId, firstName, lastName FROM teachers WHERE teacherId = :teacherId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':teacherId', $course['teacherId'], PDO::PARAM_INT);

    $statement->execute();
    $teacher = $statement->fetch(PDO::FETCH_ASSOC);

  }

  $sql = "SELECT * FROM participants p INNER JOIN participantCourses pc ON p.participantId = pc.participantId WHERE pc.courseId = :courseId;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $resultsCourseParticipants = $statement->fetchAll();


  $sql = "SELECT * FROM attendance a INNER JOIN participantCourses pc
  ON a.participantId = pc.participantId INNER JOIN courseSessions cs
  ON a.sessionId = cs.sessionId WHERE cs.courseId = :courseId AND
  cs.sessionDate < NOW();";

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
      if($row['attended']) {
        $attendanceScores[$parId]['attended']++;
      }
      $attendanceScores[$parId]['total']++;
      }
    }

  $sql = "SELECT * FROM grades g INNER JOIN assignments a
  ON a.assignmentId = g.assignmentId INNER JOIN participants p
  ON g.participantId = p.participantId WHERE a.courseId = :courseId;";

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
      $assignmentsScores[$parId]['total'] += 100;
      }
    }




} catch(PDOException $error) {
  handleError($error);
  die();
}

//get list of participants


?>
<main>
<div id="courseHeading" class="heading">
<h1><?php echo escape($course['name']); ?></h1>
<div><p><?php echo escape($course['daysOfWeek']); ?></p></div>
<div><p><?php echo escape(date('d/m/Y', strtotime($course['startDate'])) . "   hasta   " . date('d/m/Y', strtotime($course['endDate']))); ?></p></div>
<div><strong><?php if (isset($teacher)) {
  echo "Enseñado por " . escape($teacher['firstName'] . " " . $teacher['lastName']);
}?></strong></div>
</div>
<?php if (isAdministrator()) { ?>
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
      <td><?php if(count($assignmentsScores) == 0 || $assignmentsScores[$participant['participantId']]['total'] === 0) {
        echo "-";
      }
      else {
        echo escape(round((($assignmentsScores[$participant['participantId']]['score']/$assignmentsScores[$participant['participantId']]['total']) * 100)) . "%");
      }?></td>
    </tr>
  <?php } ?>
</tbody>
</table>
</div>
<?php } ?>
<?php
//ADD participants

$sql = "SELECT participantId, firstName, lastName FROM participants;";
$statement = $connection->prepare($sql);
$statement->execute();

$resultsAllParticipants = $statement->fetchAll();

 ?>

 <!-- FOR NOW I'M DOING THIS ON THE BROWSER. COULD BE SWITCHED TO AJAX LATER. DEPENDS ON INTERNET SPEED. -->
 <div id="courseActions">
   <div id="addParticipants">
   <h2>Agregar Participantes</h2>
  <input class="orange-search" type="text" id="searchBox">
  <button class="orange-submit" id="search">Buscar</button>
  <form method="post" action="actions/addParticipantsToCourse.php?courseId=<?php echo escape($courseId); ?>">
    <input class="orange-submit" type="submit" name="submit" id="submit" value="Agregar Participantes" hidden>
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
  <div id="courseManagement">
    <h2> Acciones de Maestr@ </h2>
    <div id="courseManagementButtons">
      <a href="/teachers/attendance.php?courseId=<?php echo escape($courseId);?>">
        <button type="button" id="attendanceButton" class="orange-submit">Actualizar Asistencia</button>
      </a>
      <a href="/teachers/assignments.php?courseId=<?php echo escape($courseId);?>">
        <button type="button" id="assignmentsButton" class="orange-submit">Ver Tareas</button>
      </a>
    </div>
  </div>
</div>
</main>

<?php include "templates/sidebar.php"; ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/search.js"></script>

<?php require "templates/footer.php"; ?>
