<?php

require "../common.php";
checkLogIn();

if(!isAdministrator() && !isCoordinator()) {
  echo $invalidPermissionMessage;
  die();
}


try {

    //retrieve program options to populate dropdown
    if(isAdministrator()) {

      $sql = "SELECT programId, name FROM programs";
      $statement = $connection->prepare($sql);

    } else if (isCoordinator()) {

      $sql = "SELECT p.programId AS programId, name FROM programs p INNER JOIN programCoordinators pc
      ON p.programId = pc.programId WHERE pc.coordinatorId = :participantId;";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $_SESSION['participantId'], PDO::PARAM_INT);

    }

    $statement->execute();

    if(!$statement->rowCount()) {
      echo 'no tienes acceso a ningun programa';
      die();
    }

    $resultsPrograms = $statement->fetchAll();

    $sql = "SELECT divisionId, name, programId FROM divisions;";
    $statement = $connection->prepare($sql);
    $statement->execute();

    $resultsDivisions = $statement->fetchAll();

    $sql = "SELECT p.participantId as participantId, p.firstName as firstName,
    p.lastName as lastName FROM participants p INNER JOIN participantRoles pr
    ON p.participantId = pr.participantId INNER JOIN roles r
    ON r.roleId = pr.roleId WHERE r.name = 'teacher' OR r.name = 'technician';";
    $statement = $connection->prepare($sql);
    $statement->execute();
    $resultsTeachers = $statement->fetchAll();

  } catch(PDOException $error) {
    handleError($error);
    die();
  }


include "../templates/header.php";

?>
<main>
  <div class="form-parent">
  <form method="post" action="/actions/addCourse.php" class="submit-form">
    <h2>Agregar Curso</h2>
    	<label for="courseName">Nombre de Curso:</label>
    	<input type="text" name="courseName" id="courseName" required><br>
      <?php if (count($resultsPrograms) === 1) { ?>
        <span><strong>Programa: </strong><input type="text" name="program" value="<?php echo escape($resultsPrograms[0]['name']); ?>" disabled>
          <input type="hidden" name="programId" id="programId" value="<?php echo escape($resultsPrograms[0]['programId']);?>"><br>
      <?php } else { ?>
    	<label for="programId">Programa: </label>
      <select id="programId" name="programId" required><br>
        <option value="">--Elige una opción--</option>
      <?php foreach($resultsPrograms as $row) { ?>
        <option value=<?php echo escape($row["programId"]); ?>><?php echo escape($row["name"]); ?></option>
      <?php } ?>
      </select><br>
    <?php } ?>
      <label for="divisionId">Eje: </label>
      <select id="divisionId" name="divisionId" disabled>
        <option value=''>--Elige Eje--</option>
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
      <label for="description">Descripción: </label>
      <textarea id="description" name="description" maxlength="255"></textarea><br>
      <label for="teacher">Maestr@: </label>
      <input class="orange-search" type="text" id="searchBox">
      <button type="button" class="orange-submit" id="search">Buscar</button><br>
      <div class="search-group">
          <?php foreach($resultsTeachers as $teacher) {?>
            <div class="search-row" hidden>
              <label for="teacher-<?php echo escape($teacher['participantId']);?>"><?php echo escape($teacher['firstName'] . " " . $teacher['lastName']);?></label>
              <input type="radio" id="teacher-<?php echo escape($teacher['participantId']);?>" name="teacherId" value="<?php echo escape($teacher['participantId']);?>"><br>
            </div>
          <?php } ?>
        </div>
      <input id="submit" name="submit" type="submit" value="Agregar" class="orange-submit">
  </form>
</div>
</main>

<?php include "../templates/sidebar.php"; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/search.js"></script>
 <script>var divisions = <?php echo json_encode($resultsDivisions); ?></script>
 <script src="/js/courseForm.js"></script>
<?php include "../templates/footer.php"; ?>
