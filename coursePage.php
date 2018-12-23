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

  $sql = "SELECT * FROM courses WHERE courseId= :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $resultsCourses = $statement->fetchAll();

  if(count($resultsCourses) == 0) {
    echo "ese curso ya no existe";
    die();
  }

  $course = $resultsCourses[0];

} catch(PDOException $error) {
  handleError($error);
}

//get list of participants

$sql = "SELECT * FROM participants p INNER JOIN participantCourses pc ON p.participantId = pc.participantId WHERE pc.courseId = :courseId;";
$statement = $connection->prepare($sql);
$statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
$statement->execute();

$resultsCourseParticipants = $statement->fetchAll();

?>
<div id="courseHeading">
<h1><?php echo escape($course['name']); ?></h1>
<p><?php echo escape($course['startDate'] . "   hasta   " . $course['endDate']); ?></p>
</div>
<?php if (isAdministrator()) { ?>
  <form method="post" action="admin/editCourse.php?courseId=<?php echo escape($courseId);?>">
    <input type=submit id="editCourse" class="orange-submit" value="editar curso">
  </form>
  <?php } ?>

<div id="courseInfo">
<h2>Participantes: </h2>
<?php if(count($resultsCourseParticipants) == 0) { ?>
  <p>Este curso no tiene participantes.</p>
<?php } else { ?>
<table id="participantTable">
  <thead>
    <th>Nombre</th>
    <th>Apellido</th>
    <th>Genero</th>
  </thead>
  <tbody>
  <?php foreach($resultsCourseParticipants as $participant) { ?>
    <tr>
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
 <h2>Agregar Nuevos Participantes</h2>
<input class="orange-search" type="text" id="searchBox">
<button class="orange-submit" id="search">Buscar</button>
<form method="post" action="actions/addParticipantsToCourse.php?courseId=<?php echo $courseId; ?>">
  <input class="orange-submit" type="submit" name="submit" id="submit" value="Agregar Participantes Elegidos" hidden>
   <table id="addParticipantTable">
       <thead>
         <tr class="table-head" hidden>
           <th>Nombre</th>
           <th>Apellido</th>
           <th>Select</th>
         </tr>
       </thead>
       <tbody>
         <?php foreach($resultsAllParticipants as $participant) {?>
           <tr class="table-row" hidden>
             <td class="table-cell"><?php echo escape($participant['firstName']);?></td>
             <td class="table-cell"><?php echo escape($participant['lastName']);?></td>
             <td class="table-cell"><input type="checkbox" value="check" class="select-checkbox" name="<?php echo escape($participant['participantId']); ?>"></td>
           </tr>
         <?php } ?>
       </tbody>
     </table>
</form>
</div>

 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/participantSearch.js"></script>

<?php require "templates/footer.php"; ?>
