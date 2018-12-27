<?php

require "../common.php";

include "../templates/header.php";

checkLogIn();

if(!isAdministrator()) {
  echo $invalidPermissionMessage;
}

if(isset($_GET) && isset($_GET['courseUpdated'])) {
  displayActionStatus('courseUpdated', "Curso actualizado con exito!");
}

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

  if(isset($course['teacherId'])) {

    $sql = "SELECT * FROM teachers WHERE teacherId = :teacherId";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);

    $statement->execute();

    $teacher = $statement->fetch(PDO::FETCH_ASSOC);
  }

  $sql = "SELECT * FROM teachers;";

  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsTeachers = $statement->fetchAll();


} else {

  echo "no hay curso seleccionado";
  die();

}
 ?>
 <main>
 <div class="form-parent">
 <form class = "submit-form" method="post" action="../actions/updateCourse.php?courseId=<?php echo escape($courseId); ?>">
   <h2>Editar Curso: <?php echo escape($course['name']);?></h2>
   <label for="courseName">Nombre de Curso:</label>
   <input type="text" name="courseName" id="courseName" value="<?php echo escape($course['name']);?>"><br>
   <label for="programId">Programa:</label>
   <?php if (isAdministrator()) { //only admins can changes what program a course falls under ?>
   <select id="programId" name="programId" value="<?php echo escape($course["programId"]);?>"><br>
   <?php foreach($programs as $program) {
     $programId = $course["programId"];?>
     <option value=<?php echo escape($program["programId"]); ?> <?php if($program['programId'] == $programId) {
       echo "selected= 'selected'"; } ?>><?php echo escape($program["name"]); ?></option>
   <?php } ?>
 </select><br>
<?php } ?>
   <label for="startDate">Inicio:</label>
   <input id = "startDate" name="startDate" type="date" value="<?php echo escape($course['startDate']); ?>"></input><br>
   <label for="endDate">Finalización:</label>
   <input id="endDate" type="date" name="endDate" value="<?php echo escape($course['endDate']); ?>"></input><br>
   <label for="teacher">Maestr@: <span id="course-teacher"><?php if( isset($teacher)) {
     echo escape($teacher['firstName'] . ' ' . $teacher['lastName']);
   }?> </label><br>
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
   <input name="submit" type="submit" value="Actualizar" class="orange-submit">
 </form>
</div>
</main>
<?php include "../templates/sidebar.php";?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/search.js"></script>
<?php include "../templates/footer.php"; ?>
