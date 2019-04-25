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

    $sql = "SELECT teacherId, programId, name, description, daysOfWeek, startDate, endDate, divisionId
    FROM courses_View WHERE courseId = :courseId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    if($statement->rowCount() === 0) {
      echo "ese curso ya no existe";
      die();
    }

    $course = $statement->fetch(PDO::FETCH_ASSOC);
    $programId = $course["programId"];
    $daysOfWeek = $course['daysOfWeek'];

    $sql = "SELECT programId, name FROM programs;";
    $statement = $connection->prepare($sql);
    $statement->execute();

    $programs = $statement->fetchAll();

    $resultsPrograms = $statement->fetchAll();

    $sql = "SELECT divisionId, name, programId FROM divisions;";
    $statement = $connection->prepare($sql);
    $statement->execute();

    $resultsDivisions = $statement->fetchAll();

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
    ON r.roleId = pr.roleId WHERE p.alive AND (r.name = 'teacher' OR r.name = 'technician');";
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
   <?php if (isAdministrator()) { //only admins can changes what program a course falls unde ?>
  <label for="programId">Programa:</label>
   <select id="programId" name="programId" value="<?php echo escape($course["programId"]);?>"><br>
   <?php foreach($programs as $program) { ?>
     <option value=<?php echo escape($program["programId"]); ?> <?php if($program['programId'] == $programId) {
       echo "selected= 'selected'"; } ?>><?php echo escape($program["name"]); ?></option>
   <?php
 } ?>
 </select><br>
<?php } ?>
   <label for="divisionId">Eje: </label>
   <select id="divisionId" name="divisionId">
     <option value=''>--Elige un Eje--</option>
     <?php
     $programDivisions = array_filter($resultsDivisions, function($elem) {
       global $programId;
       return $elem['programId'] === $programId;
     });
     foreach($programDivisions as $division) { ?>
       <option value=<?php echo escape($division['divisionId']); ?> <?php echo($division['divisionId'] == $course['divisionId'] ? 'selected' : ''); ?>>
         <?php echo escape($division['name']); ?>
       </option>
     <?php } ?>
   </select><br>
   <label for="startDate">Inicio:</label>
   <input id = "startDate" name="startDate" type="date" min=<?php echo escape(date('Y-m-d', time())); ?> value="<?php echo escape($course['startDate']); ?>"
    <?php if(date('Y-m-d', time()) > $course['startDate']) { ?> disabled <?php } ?>></input><br>
   <label for="endDate">Finalización:</label>
   <input id="endDate" type="date" name="endDate" min=<?php echo escape(max(date('Y-m-d', time()), $course['startDate'])); ?> value="<?php echo escape($course['endDate']); ?>"
      <?php if(date('Y-m-d', time()) > $course['endDate']) { ?> disabled <?php } ?>></input><br>
   <label for="schedule">Horario: </label><br>
   <label for="sunday">D <input type="checkbox" id="sunday" name="sunday" value="sunday" <?php if(stripos($daysOfWeek, 'Sunday') !== FALSE) { ?>checked<?php } ?>></label>
   <label for="monday">L <input type="checkbox" id="monday" name="monday" value="monday" <?php if(stripos($daysOfWeek, 'Monday') !== FALSE) { ?>checked<?php } ?>></label>
   <label for="tuesday">Ma <input type="checkbox" id="tuesday" name="tuesday" value="tuesday" <?php if(stripos($daysOfWeek, 'Tuesday') !== FALSE) { ?>checked<?php } ?>></label>
   <label for="wednesday">Mi <input type="checkbox" id="wednesday" name="wednesday" value="wednesday" <?php if(stripos($daysOfWeek, 'Wednesday') !== FALSE) { ?>checked<?php } ?>></label>
   <label for="thursday">J <input type="checkbox" id="thursday" name="thursday" value="thursday" <?php if(stripos($daysOfWeek, 'Thursday') !== FALSE) { ?>checked<?php } ?>></label>
   <label for="friday">V <input type="checkbox" id="friday" name="friday" value="friday" <?php if(stripos($daysOfWeek, 'Friday') !== FALSE) { ?>checked<?php } ?>></label>
   <label for="saturday">S <input type="checkbox" id="saturday" name="saturday" value="saturday" <?php if(stripos($daysOfWeek, 'Saturday') !== FALSE) { ?>checked<?php } ?>></label><br>
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
<script>const divisions = <?php echo json_encode($resultsDivisions); ?></script>
<script>const selectedDivision = <?php echo IS_NULL($course['divisionId']) ? 0 : $course['divisionId']; ?></script>
 <script src="/js/search.js"></script>
 <script src="/js/deleteButton.js"></script>
 <script src="/js/courseForm.js"></script>
<?php include "../templates/footer.php"; ?>
