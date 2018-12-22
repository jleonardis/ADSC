<?php

require "common.php";
checkLogIn();

include "templates/header.php";
if(!checkPermission()) {
  echo "fail";
  die();
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
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_STR);
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
$statement->bindParam(':courseId', $courseId, PDO::PARAM_STR);
$statement->execute();

$resultsCourseParticipants = $statement->fetchAll();


//ADD participants

$sql = "SELECT participantId, firstName, lastName FROM participants;";
$statement = $connection->prepare($sql);
$statement->execute();

$resultsAllParticipants = $statement->fetchAll();

 ?>

 FOR NOW I'M DOING THIS ON THE BROWSER. COULD BE SWITCHED TO AJAX LATER. DEPENDS ON INTERNET SPEED.
<input type="text" id="searchBox" value="Buscar Participantes">
<button id="search">Buscar</button>
<form method="post" action="actions/addParticipantsToCourse.php?courseId=<?php echo $courseId; ?>">
  <input type="submit" name="submit" id="submit" value="Agregar Participantes Elegidos" hidden>
   <table id="participantTable">
       <thead>
         <tr class="table-row" hidden>
           <th>Nombre</th>
           <th>Apellido</th>
           <th>Select</th>
         </tr>
       </thead>
       <tbody>
         <?php foreach($resultsAllParticipants as $participant) {?>
           <tr class="table-row" id="participant-<?php echo $participant['participantId']?>" hidden>
             <td class="table-cell"><?php echo $participant['firstName'];?></td>
             <td class="table-cell"><?php echo $participant['lastName'];?></td>
             <td class="table-cell"><input type="checkbox" value="check" class="select-checkbox" name="<?php echo $participant['participantId'] ?>"></td>
           </tr>
         <?php } ?>
       </tbody>
     </table>
</form>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/coursePage.js"></script>

<?php require "templates/footer.php"; ?>
