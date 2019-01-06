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
  $sql = "SELECT * FROM courses WHERE alive = 1 ORDER BY endDate";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsCourses = $statement->fetchAll();


} catch(PDOException $error) {

  handleError($error);
  die();

}

require "templates/header.php";
displayActionStatus('courseAdded', 'Curso agregado con exito!');
displayActionStatus('programAdded', 'Programa agregado con exito!');

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
  <thead>
    <tr>
      <th class="table-head" hidden>Curso</th>
      <th class="table-head" hidden>Inicio</th>
      <th class="table-head" hidden>Finalización</th>
    </tr>
  </thead>
  <tbody>

    <?php foreach($resultsCourses as $row) { ?>
      <tr class="course-link course-row course-row-<?php echo escape($row["programId"])?>" data-href="coursePage.php?courseId=<?php echo $row["courseId"];?>" hidden>
        <td><?php echo escape($row["name"]); ?></td>
        <td><?php echo escape($row["startDate"]); ?></td>
        <td><?php echo escape($row["endDate"]); ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>

</div>
</main>

<?php include "templates/sidebar.php"; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/courses.js"></script>
<script src="js/search.js"></script>

<?php require "templates/footer.php" ?>
