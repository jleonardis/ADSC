<?php
require "common.php";
checkLogIn();

require "templates/header.php";

if(isset($_GET['courseAdded'])) {
  if($_GET['courseAdded'] == 1) {
    ?><h2>Curso agregado con exito!</h2><?php
  }
  else {
    ?><h2>Algo fállo. Curso no fue agregado.</h2><?php
  }
}
//display input form if nothing has been submitted

try {

  //retrieve program options to populate dropdown
  $sql = "SELECT programId, name FROM programs";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsPrograms = $statement->fetchAll();

  //retrieve all courses
  $sql = "SELECT * FROM courses";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsCourses = $statement->fetchAll();

} catch(PDOException $error) {

  echo $error->getMessage();

}

?>

<div id="courseList">
<h1>Buscar Cursos</h1>

<select id="programSelect">
  <option value="">--Elige un programa--</option>
  <?php foreach($resultsPrograms as $row) { ?>
    <option value=<?php echo escape($row["programId"]); ?>><?php echo escape($row["name"]); ?></option>
  <?php } ?>
</select>

<table id="courseTable">
  <thead>
    <tr>
      <th class="table-head" hidden>Nombre de Curso</th>
      <th class="table-head" hidden>Fecha de Inicio</th>
      <th class="table-head" hidden>Fecha de Finalización</th>
    </tr>
  </thead>
  <tbody>

    <?php foreach($resultsCourses as $row) { ?>
      <tr class="course-row" data-href="coursePage.php?courseId=<?php echo $row["courseId"];?>">
        <td class="table-cell table-<?php echo escape($row["programId"])?>" hidden><?php echo escape($row["name"]); ?></td>
        <td class="table-cell table-<?php echo escape($row["programId"])?>" hidden><?php echo escape($row["startDate"]); ?></td>
        <td class="table-cell table-<?php echo escape($row["programId"])?>" hidden><?php echo escape($row["endDate"]); ?></td>
      </tr>
    <?php } ?>
  </tbody>
<table>

</div>

<div id="addCourse">
  <h1>Agregar Curso Nuevo</h1>

  <form method="post" action="actions/addCourse.php">
  	<label for="courseName">Nombre de Curso:</label>
  	<input type="text" name="courseName" id="courseName" required>
  	<label for="program">Programa:</label>
    <select id="program" name="program" required>
      <option value="">--Elige una opción--</option>
    <?php foreach($resultsPrograms as $row) { ?>
      <option value=<?php echo escape($row["programId"]); ?>><?php echo escape($row["name"]); ?></option>
    <?php } ?>
    </select>
  	<label for="startDate">Fecha de Inicio:</label>
    <input id = "startDate" name="startDate" type="date"></input>
    <label for="endDate">Fecha de Finalización:</label>
    <input id="endDate" type="date" name="endDate"></input>
    <input name="submit" type="submit" value="Submit">
  </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/courses.js"></script>
<?php


require "templates/footer.php" ?>
