<?php

require "../common.php";

include "../templates/header.php";

checkLogIn();

if(!hasAdminPermission()) {
  echo $invalidPermissionMessage;
  die();
}

if(isset($_GET['courseId'])) {

  $courseId = $_GET['courseId'];

  if(!hasPermission($courseId)) {
    echo $invalidPermissionMessage;
    die();
  }

  try
  {

    $sql = "SELECT teacherId, programId, name, description FROM courses WHERE courseId = :courseId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    if($statement->rowCount() === 0) {
      echo "ese curso ya no existe";
      die();
    }

    $course = $statement->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT programId, name FROM programs;";
    $statement = $connection->prepare($sql);
    $statement->execute();

    $programs = $statement->fetchAll();

    if($course['teacherId']) {

      $teacherId = $course['teacherId'];
      $sql = "SELECT firstName, lastName, participantId
      FROM participants WHERE participantId = :teacherId";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);

      $statement->execute();

      $currentTeacher = $statement->fetch(PDO::FETCH_ASSOC);

    }

    $sql = "SELECT firstName, lastName, p.participantId as participantId FROM participants p INNER JOIN participantRoles pr
    ON p.participantId = pr.participantId INNER JOIN roles r
    ON r.roleId = pr.roleId WHERE r.name = 'teacher' OR r.name = 'technician';";
    $statement = $connection->prepare($sql);
    $statement->execute();
    $resultsTeachers = $statement->fetchAll();
  }
  catch (PDOException $error) {
    handleError($error);
    die();
  }


} else {

  echo "no hay curso seleccionado";
  die();

}
 ?>
 <main>
 <div class="form-parent">
 <form class = "submit-form" method="post" action="../actions/updateCourse.php?courseId=<?php echo escape($courseId); ?>&currentTeacherId=<?php echo escape($course['teacherId']);?>">
   <h2>Editar Curso: <?php echo escape($course['name']);?></h2>
   <label for="courseName">Nombre de Curso:</label>
   <input type="text" name="courseName" id="courseName" value="<?php echo escape($course['name']);?>"><br>
   <?php if (isAdministrator()) { //only admins can changes what program a course falls under ?>
  <label for="programId">Programa:</label>
   <select id="programId" name="programId" value="<?php echo escape($course["programId"]);?>"><br>
   <?php foreach($programs as $program) {
     $programId = $course["programId"];?>
     <option value=<?php echo escape($program["programId"]); ?> <?php if($program['programId'] == $programId) {
       echo "selected= 'selected'"; } ?>><?php echo escape($program["name"]); ?></option>
   <?php } ?>
 </select><br>
<?php } ?>
   <!-- <label for="startDate">Inicio:</label>
   <input id = "startDate" name="startDate" type="date" value="<?php echo escape($course['startDate']); ?>"></input><br>
   <label for="endDate">Finalización:</label>
   <input id="endDate" type="date" name="endDate" value="<?php echo escape($course['endDate']); ?>"></input><br> -->
   <label for="description">Descripción: </label>
   <textarea id="description" name="description" maxlength="255"><?php echo escape($course['description']); ?></textarea><br>
   <input class="orange-search" type="text" id="searchBox" value="<?php echo escape(isset($currentTeacher)?$currentTeacher['firstName'] . ' ' . $currentTeacher['lastName']:'');?>">
   <button type="button" class="orange-submit" id="search">Buscar Maestr@</button><br>
   <div class="search-group">
       <?php foreach($resultsTeachers as $teacher) {?>
         <div class="search-row" hidden>
         <label for="teacher-<?php echo escape($teacher['participantId']);?>"><?php echo escape($teacher['firstName'] . " " . $teacher['lastName']);?></label>
       <input type="radio" id="teacher-<?php echo escape($teacher['participantId']);?>" name="teacherId" value="<?php echo escape($teacher['participantId']);?>"
       <?php echo escape(isset($currentTeacher) && $teacher['participantId'] === $currentTeacher['participantId']?'checked':'');?>><br>
     </div>
       <?php } ?>
     </div>
   <input name="submit" type="submit" value="Actualizar" class="orange-submit">
   <button id="cancelCourse" type="button" class="orange-submit delete-button" data-href="/actions/cancelCourse.php?courseId=<?php echo escape($courseId)?>">Eliminar Curso</button>
 </form>
</div>
</main>
<?php include "../templates/sidebar.php";?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/search.js"></script>
 <script src="/js/deleteButton.js"></script>
<?php include "../templates/footer.php"; ?>
