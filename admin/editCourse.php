<?php

require "../common.php";

include "../templates/header.php";

checkLogIn();

if(!isAdministrator()) {
  echo $invalidPermissionMessage;
}

displayActionStatus('courseUpdated', "Curso actualizado con exito!");

if(isset($_GET['courseId'])) {

  $courseId = $_GET['courseId'];

  $sql = "SELECT * FROM courses WHERE courseId = :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $result = $statement->fetchAll();

  if(count($result) == 0) {
    echo "ese curso ya no existe";
    die();
  }

  $course = $result[0];

  $sql = "SELECT * FROM programs;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $programs = $statement->fetchAll();

} else {

  echo "no hay curso seleccionado";
  die();

}
 ?>
<h2>Editar Curso: <?php echo escape($course['name']);?></h2>
 <form method="post" action="../actions/updateCourse.php?courseId=<?php echo escape($courseId); ?>">
   <label for="courseName">Nombre de Curso:</label>
   <input type="text" name="courseName" id="courseName" value="<?php echo escape($course['name']);?>"><br>
   <label for="programId">Programa:</label>
   <select id="programId" name="programId" value="<?php echo escape($course["programId"]);?>"><br>
   <?php foreach($programs as $program) { ?>
     <option value=<?php echo escape($program["programId"]); ?>><?php echo escape($program["name"]); ?></option>
   <?php } ?>
  </select><br>
   <label for="startDate">Fecha de Inicio:</label>
   <input id = "startDate" name="startDate" type="date" value="<?php echo escape($course['startDate']); ?>"></input>
   <label for="endDate">Fecha de Finalización:</label>
   <input id="endDate" type="date" name="endDate" value="<?php echo escape($course['endDate']); ?>"></input><br>
   <input name="submit" type="submit" value="Submitir Cambios" class="orange-submit">
 </form>

<?php include "../templates/footer.php"; ?>
