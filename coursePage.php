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

//get course info
try {

  //NOTE: CONSIDER COMBINING THESE SELECT STATEMENTS

  $sql = "SELECT teacherId, c.name as name, c.description, daysOfWeek, cs.startDate,
    cs.endDate, d.name as divisionName
  FROM
    (
      SELECT courseId, teacherId, name, description, daysOfWeek, divisionId
      FROM courses
      WHERE courseId = :courseId
    ) c
  LEFT JOIN
    (
      SELECT courseId, MIN(sessionDate) AS startDate, MAX(sessionDate) AS endDate
      FROM courseSessions
      WHERE alive
      AND courseId = :courseId
      GROUP BY courseId
    ) cs
    ON c.courseId = cs.courseId
  LEFT JOIN divisions d
    ON d.divisionId = c.divisionId;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() == 0) {
    echo "ese curso ya no existe";
    die();
  }

  $course = $statement->fetch(PDO::FETCH_ASSOC);

  $daysOfWeek = $course['daysOfWeek'];
  $daysOfWeek = translateToSpanish($daysOfWeek);
  $daysOfWeek = str_replace(',', ', ', $daysOfWeek);

  $hasDates = !IS_NULL($course['startDate']);

  if($course['teacherId']) {

    $sql = "SELECT participantId, firstName, lastName FROM participants
    WHERE participantId = :teacherId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':teacherId', $course['teacherId'], PDO::PARAM_INT);

    $statement->execute();
    $teacher = $statement->fetch(PDO::FETCH_ASSOC);

  }

  // $sql = "SELECT participantId, firstName, lastName, gender, hasDPI
  // FROM currentParticipantCourses_View
  // WHERE courseId = :courseId
  // ORDER BY hasDPI DESC, lastName ASC;";
  // $statement = $connection->prepare($sql);
  // $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  // $statement->execute();
  //
  // $resultsCourseParticipants = $statement->fetchAll();
  //
  //
  // $sql = "SELECT pc.participantId as participantId, attended
  // FROM attendance a JOIN currentParticipantCourses_View pc
  // ON a.participantId = pc.participantId JOIN courseSessions cs
  // ON a.sessionId = cs.sessionId AND pc.courseId = cs.courseId
  // WHERE cs.courseId = :courseId AND
  // cs.sessionDate < NOW() AND cs.alive;";
  //
  // $statement = $connection->prepare($sql);
  // $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  // $statement->execute();
  //
  // $attendanceScores = array();
  //
  // if($statement->rowCount() != 0) {
  //   $resultsAttendance = $statement->fetchAll();
  //   //used to calculate percentage attendance (calculated in the html)
  //   foreach($resultsAttendance as $row) {
  //     $parId = $row['participantId'];
  //     if(!isset($attendanceScores[$parId])){
  //       $attendanceScores[$parId] = array(
  //         'attended' => 0,
  //         'total' => 0);
  //     }
  //     if($row['attended'] === 'present') {
  //       $attendanceScores[$parId]['attended']++;
  //     }
  //     if($row['attended'] !== 'excused'){
  //       $attendanceScores[$parId]['total']++;
  //     }
  //   }
  // }
  //
  // $sql = "SELECT p.participantId as participantId, grade, hasDPI
  // FROM grades g JOIN assignments a
  // ON a.assignmentId = g.assignmentId JOIN currentParticipantCourses_View p
  // ON g.participantId = p.participantId AND p.courseId = a.courseId
  // WHERE a.courseId = :courseId AND a.alive;";
  //
  // $statement = $connection->prepare($sql);
  // $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  // $statement->execute();
  //
  // $assignmentsScores = array();
  //
  // if($statement->rowCount() != 0) {
  //   $resultsAssignments = $statement->fetchAll();
  //   //used to calculate percentage attendance (calculated in the html)
  //   foreach($resultsAssignments as $row) {
  //     $parId = $row['participantId'];
  //     if(!isset($assignmentsScores[$parId])){
  //       $assignmentsScores[$parId] = array(
  //         'score' => 0,
  //         'total' => 0,
  //         'hasDPI' => $row['hasDPI']);
  //     }
  //     if($row['grade']) {
  //       $assignmentsScores[$parId]['score'] += $row['grade'];
  //     }
  //     if($row['grade'] !== null){
  //       $assignmentsScores[$parId]['total'] += 100;
  //     }
  //   }
  // }

$sql = "SELECT p.firstName, p.lastName, p.gender, stats.*
FROM participants p
JOIN
	(
	SELECT pc.participantId, IFNULL(ROUND((SUM(CASE WHEN att.attended = 'present' THEN 1 ELSE 0 END) /
		SUM(CASE WHEN att.attended = 'absent' OR att.attended = 'present' THEN 1 ELSE 0 END)) * 100), '-') as attendancePercent,
		IFNULL(ROUND((SUM(g.grade)/ (SUM(CASE WHEN g.grade IS NULL THEN 0 ELSE 1 END)*100)) * 100), '-') as gradesPercent,
		MAX(CASE WHEN pc.dropOutDate IS NULL THEN 1 ELSE 0 END) AS active
	FROM participantCourses pc
	LEFT JOIN
		(
		SELECT sessionId, courseId
		FROM courseSessions
		WHERE sessionDate <= CURDATE()
		) cs ON cs.courseId = pc.courseId
	LEFT JOIN attendance att ON att.sessionId = cs.sessionId AND att.participantId = pc.participantId
	LEFT JOIN assignments ass ON ass.courseId = pc.courseId
	LEFT JOIN grades g ON g.assignmentId = ass.assignmentId AND g.participantId = pc.participantId
	WHERE pc.courseId = :courseId
	GROUP BY pc.participantId
    ) stats
ON stats.participantId = p.participantId
ORDER BY stats.active DESC;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $results = $statement->fetchAll();

} catch(PDOException $error) {
  handleError($error);
  die();
}

//get list of participants

if(hasAdminPermission() || isTechnician()) {
  try {
    $sql = "SELECT participantId, firstName, lastName FROM participants p
    WHERE NOT EXISTS (SELECT 1 FROM participantCourses pc
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
<?php if($course['divisionName']) { echo "<h4>" . escape($course['divisionName']) . "</h4>"; } ?>
<div><strong><?php echo escape($course['description']); ?></strong></div>
</div>
<?php if($hasDates) { ?>
<div>
  <?php if($daysOfWeek) {echo "<p>" . escape($daysOfWeek) . "</p><br>";} ?>
  <?php echo escape(date('d/m/Y', strtotime($course['startDate'])) . "   hasta   " . date('d/m/Y', strtotime($course['endDate']))); ?></p>
</div>
<?php } ?>
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


<div id="courseActions">
 <div id="courseManagement">
   <div id="courseManagementButtons">
     <a href="/teachers/attendance.php?courseId=<?php echo escape($courseId);?>">
       <button type="button" id="attendanceButton" class="orange-submit">Horario y Asistencia</button>
     </a>
     <a href="/teachers/assignments.php?courseId=<?php echo escape($courseId);?>">
       <button type="button" id="assignmentsButton" class="orange-submit">Tareas</button>
     </a>
     <a href="/teachers/quotas.php?courseId=<?php echo escape($courseId); ?>">
       <button type="button" id="quotasButton" class="orange-submit">Cuotas</button>
     </a>
   </div>
 </div>

<div id="courseInfo">
<h2>Participantes: </h2>
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
    <?php
    $firstInactive = false;
    foreach($results as $row) { ?>
      <?php if(!$row['active'] && !$firstInactive) { ?>
        <tr><td>-</td></tr>
        <tr><td><strong>Retirados</strong></td></tr>
      <?php
      $firstInactive = true;
      } ?>
      <tr class="participant-row<?php if(!$row['active']) { echo " inactive-participant"; }?>" data-href="/participantPage.php?participantId=<?php echo escape($row['participantId']);?>">
        <td><?php echo escape($row['firstName']); ?></td>
        <td><?php echo escape($row['lastName']); ?></td>
        <td class="centered-cell"><?php echo escape($row['gender']); ?></td>
        <td class="centered-cell"><?php echo ($row['attendancePercent'] === '-' ? '' : "%") . escape($row['attendancePercent']);?></td>
        <td class="centered-cell"><?php echo ($row['gradesPercent'] === '-' ? '' : "%") . escape($row['gradesPercent']);?></td>
        <?php if($row['active']) { ?>
          <td class="remove-participant centered-cell" style="color: red; text-transform: uppercase;"
          data-href="/actions/removeParticipantFromCourse.php?participantId=<?php echo escape($row['participantId']); ?>&courseId=<?php echo escape($courseId);?>">Quitar</td>
        <?php } else { ?>
          <td class="reactivate-participant centered-cell" style="color: green; text-transform: uppercase;"
          data-href="/actions/addParticipantsToCourse.php?participantId=<?php echo escape($row['participantId']); ?>&courseId=<?php echo escape($courseId);?>">Reagregar</td>
        <?php } ?>
      </tr>
    <?php } ?>
</tbody>
</table>
</div>

  <?php if (hasAdminPermission() || isTechnician()){ ?>
  <div id="addParticipants">
  <h2>Agregar Participantes</h2>
 <form method="post" action="actions/addParticipantsToCourse.php?courseId=<?php echo escape($courseId); ?>">
   <input class="orange-search" type="text" id="searchBox">
   <button type="button" class="orange-submit" id="search">Buscar</button>
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
 <script src="js/coursePage.js"></script>

<?php require "templates/footer.php"; ?>
