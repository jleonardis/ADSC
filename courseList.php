<?php
require "common.php";
checkLogIn();

//display input form if nothing has been submitted

try {

  //retrieve program options to populate dropdown
  $sql = "SELECT programId, name FROM programs";
  if(isCoordinator()) {
    $sql = "SELECT p.programId AS programId, name FROM programs p INNER JOIN programCoordinators pc ON p.programId = pc.programId
    WHERE pc.coordinatorId = :participantId;";
    $participantId = $_SESSION['participantId'];
  }
  $statement = $connection->prepare($sql);
  if(isCoordinator()){
    $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  }
  $statement->execute();
  if(!$statement->rowCount()){
    echo "no tienes acceso a ningun programa";
    die();
  }
  $resultsPrograms = $statement->fetchAll();

  //retrieve all courses
  $sql = "SELECT courseId, c.name, c.programId, d.divisionId, d.name as divisionName,
   startDate, endDate, 1 sortby
  FROM courses_View c
  JOIN divisions d ON d.divisionId = c.divisionId
  WHERE c.alive
  UNION ALL
  SELECT courseId, name, programId, 0 as divisionId, 'Sin Eje' as divisionName,
    startDate, endDate, 2 sortby
  FROM courses_View
  WHERE alive AND divisionId IS NULL
  ORDER BY sortby, programId, divisionName, endDate";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsCourses = $statement->fetchAll();


} catch(PDOException $error) {

  handleError($error);
  die();

}

require "templates/header.php";

?>
<main>
<div id="courseList">
<h2>Buscar Cursos</h2>

<select class="orange-dropdown" id="programSelect">
  <?php if(count($resultsPrograms) > 1) { ?>
    <option value="">--Elige un programa--</option>
  <?php } ?>
  <?php foreach($resultsPrograms as $row) { ?>
    <option value=<?php echo escape($row["programId"]); ?>><?php echo escape($row["name"]); ?></option>
  <?php } ?>
</select>

<table id="courseTable">
  <tbody>
    <?php
    $currentDivisionId = -1;
    $currentProgramId = -1;
    foreach($resultsCourses as $row) {
      if($row['divisionId'] !== $currentDivisionId || $row['programId'] !== $currentProgramId) {
        $currentDivisionId = $row['divisionId'];
        $currentProgramId = $row['programId'];?>
      <tr class="course-row course-row-<?php echo escape($row['programId']); ?>" hidden><th class="division-heading"><strong style="text-transform: uppercase;"><?php echo escape($row['divisionName']); ?></strong></th></tr>
        <tr class="course-row course-row-<?php echo escape($row['programId']); ?>">
          <th class="table-head" hidden><strong>Curso</strong></th>
          <th class="table-head" hidden><strong>Inicio</strong></th>
          <th class="table-head" hidden><strong>Finalización</strong></th>
        </tr>
    <?php } ?>
      <tr class="course-link course-row course-row-<?php echo escape($row["programId"])?>" data-href="coursePage.php?courseId=<?php echo $row["courseId"];?>" hidden>
        <td><?php echo escape($row["name"]); ?></td>
        <td><?php echo escape(date('d/m/Y', strtotime($row['startDate']))); ?></td>
        <td><?php echo escape(date('d/m/Y', strtotime($row['endDate']))); ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>

</div>
</main>

<?php include "templates/sidebar.php"; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/courseList.js"></script>
<script src="js/search.js"></script>

<?php require "templates/footer.php" ?>
