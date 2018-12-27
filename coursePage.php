<?php

require "common.php";
checkLogIn();

include "templates/header.php";
if(!hasPermission()) {
  echo "No tienes permiso para ver este curso";
  die();
}

if(isset($_GET['participantsAdded'])) {
  displayActionStatus('participantsAdded', 'Participante(s) agregado con exito!');
}

if(!isset($_GET['courseId'])) {
  echo "no hay curso seleccionado";
  die();
}

$courseId = $_GET['courseId'];

//get course info
try {

  $sql = "SELECT * FROM courses WHERE courseId = :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() == 0) {
    echo "ese curso ya no existe";
    die();
  }

  $course = $statement->fetch(PDO::FETCH_ASSOC);

  if($course['teacherId']) {

    $sql = "SELECT teacherId, firstName, lastName FROM teachers WHERE teacherId = :teacherId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':teacherId', $course['teacherId'], PDO::PARAM_INT);

    $statement->execute();
    $teacher = $statement->fetch(PDO::FETCH_ASSOC);

  }

  $sql = "SELECT * FROM participants p INNER JOIN participantCourses pc ON p.participantId = pc.participantId WHERE pc.courseId = :courseId;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $resultsCourseParticipants = $statement->fetchAll();

} catch(PDOException $error) {
  handleError($error);
}

//get list of participants



?>
<main>
<div id="courseHeading" class="heading">
<h1><?php echo escape($course['name']); ?></h1>
<div><p><?php echo escape($course['startDate'] . "   hasta   " . $course['endDate']); ?></p></div>
<div><strong><?php if (isset($teacher)) {
  echo "Enseñado por " . escape($teacher['firstName'] . " " . $teacher['lastName']);
}?></strong></div>
</div>
<?php if (isAdministrator()) { ?>
  <form method="post" action="admin/editCourse.php?courseId=<?php echo escape($courseId);?>">
    <input type=submit id="editCourse" class="orange-submit edit-button" value="editar">
  </form>
<?php } ?>

<div id="courseInfo">
<h2>Participantes: </h2>
<?php if(count($resultsCourseParticipants) == 0) { ?>
  <p>Este curso no tiene participantes.</p>
<?php } else { ?>
<table id="participantList">
  <thead>
    <th>Nombre</th>
    <th>Apellido</th>
    <th>Género</th>
  </thead>
  <tbody>
  <?php foreach($resultsCourseParticipants as $participant) { ?>
    <tr class="participant-row" data-href="/participantPage.php?participantId=<?php echo escape($participant['participantId']);?>">
      <td><?php echo $participant['firstName'];?></td>
      <td><?php echo $participant['lastName'];?></td>
      <td><?php echo $participant['gender'];?></td>
    </tr>
  <?php } ?>
</tbody>
</table>
</div>
<?php } ?>
<?php
//ADD participants

$sql = "SELECT participantId, firstName, lastName FROM participants;";
$statement = $connection->prepare($sql);
$statement->execute();

$resultsAllParticipants = $statement->fetchAll();

 ?>

 <!-- FOR NOW I'M DOING THIS ON THE BROWSER. COULD BE SWITCHED TO AJAX LATER. DEPENDS ON INTERNET SPEED. -->
 <div id="addParticipants">
 <h2>Agregar Participantes</h2>
<input class="orange-search" type="text" id="searchBox">
<button class="orange-submit" id="search">Buscar</button>
<form method="post" action="actions/addParticipantsToCourse.php?courseId=<?php echo escape($courseId); ?>">
  <input class="orange-submit" type="submit" name="submit" id="submit" value="Agregar Participantes" hidden>
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
</main>

<?php include "templates/sidebar.php"; ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/search.js"></script>

<?php require "templates/footer.php"; ?>
