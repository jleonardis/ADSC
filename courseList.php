<?php
require "common.php";
checkLogIn();

require "templates/header.php";

if(isset($_GET['courseAdded'])) {
  displayActionStatus('courseAdded', 'Curso agregado con exito!');
}
//display input form if nothing has been submitted

try {

  //retrieve program options to populate dropdown
  $sql = "SELECT programId, name FROM programs";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsPrograms = $statement->fetchAll();

  //retrieve all courses
  $sql = "SELECT * FROM courses ORDER BY endDate";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsCourses = $statement->fetchAll();

  $sql = "SELECT * FROM teachers;";
  $statement = $connection->prepare($sql);
  $statement->execute();
  $resultsTeachers = $statement->fetchAll();


} catch(PDOException $error) {

  echo $error->getMessage();

}

?>
<main>
<div id="courseList">
<h2>Buscar Cursos</h2>

<select class="orange-dropdown" id="programSelect">
  <option value="">--Elige un programa--</option>
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
      <tr class="course-row course-row-<?php echo escape($row["programId"])?>" data-href="coursePage.php?courseId=<?php echo $row["courseId"];?>" hidden>
        <td><?php echo escape($row["name"]); ?></td>
        <td><?php echo escape($row["startDate"]); ?></td>
        <td><?php echo escape($row["endDate"]); ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>

</div>

<?php if(isAdministrator()) { ?>
<form method="post" action="actions/addCourse.php" class="submit-form">
  <h2>Agregar Curso</h2>
  	<label for="courseName">Nombre de Curso:</label>
  	<input type="text" name="courseName" id="courseName" required><br>
  	<label for="program">Programa:</label>
    <select id="program" name="program" required><br>
      <option value="">--Elige una opción--</option>
    <?php foreach($resultsPrograms as $row) { ?>
      <option value=<?php echo escape($row["programId"]); ?>><?php echo escape($row["name"]); ?></option>
    <?php } ?>
    </select><br>
  	<label for="startDate">Inicio: </label>
    <input id = "startDate" name="startDate" type="date" required><br>
    <label for="endDate">Finalización: </label>
    <input id="endDate" type="date" name="endDate" required><br>
    <label for="schedule">Horario: </label><br>
    <label for="sunday">D <input type="checkbox" id="sunday" name="sunday" value="sunday"></label>
    <label for="monday">L <input type="checkbox" id="monday" name="monday" value="monday"></label>
    <label for="tuesday">Ma <input type="checkbox" id="tuesday" name="tuesday" value="tuesday"></label>
    <label for="wednesday">Mi <input type="checkbox" id="wednesday" name="wednesday" value="wednesday"></label>
    <label for="thursday">J <input type="checkbox" id="thursday" name="thursday" value="thursday"></label>
    <label for="friday">V <input type="checkbox" id="friday" name="friday" value="friday"></label>
    <label for="saturday">S <input type="checkbox" id="saturday" name="saturday" value="saturday"></label><br>
    <label for="teacher">Maestr@: </label>
    <input class="orange-search" type="text" id="searchBox">
    <button type="button" class="orange-submit" id="search">Buscar</button><br>
    <div class="search-group">
        <?php foreach($resultsTeachers as $teacher) {?>
          <div class="search-row" hidden>
            <label for="teacher-<?php echo escape($teacher['teacherId']);?>"><?php echo escape($teacher['firstName'] . " " . $teacher['lastName']);?></label>
            <input type="radio" id="teacher-<?php echo escape($teacher['teacherId']);?>" name="<?php echo escape($teacher['teacherId']);?>"><br>
          </div>
        <?php } ?>
      </div>
    <input id="submit" name="submit" type="submit" value="Agregar" class="orange-submit">
</form>
<?php } ?>
</main>

<?php include "templates/sidebar.php"; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/courses.js"></script>
<script src="js/search.js"></script>

<?php require "templates/footer.php" ?>
