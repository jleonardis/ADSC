<?php

require "common.php";
checkLogIn();

include "templates/header.php";

if(!isset($_GET['participantId'])) {
  echo "no hay participante seleccionado";
  die();
}

$participantId = $_GET['participantId'];

if(!hasPermission(0, 0, $participantId)) {
  echo "no tienes permiso para usar esta pagina";
  die();
}

//get participant info
try {

  $sql = "SELECT * FROM participants WHERE participantId = :participantId;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() == 0) {
    echo "ese participante ya no esta en la base de datos";
    die();
  }

  $participant = $statement->fetch(PDO::FETCH_ASSOC);
  $participant['age'] = getAge(new DateTime($participant['dob']));

  $sql = "SELECT * FROM participantCourses pc INNER JOIN courses c ON pc.courseId = c.courseId
  WHERE pc.participantId = :participantId AND NOW() < ADDDATE(c.endDate, INTERVAL 1 MONTH)
  AND NOW() > SUBDATE(c.startDate, INTERVAL 1 MONTH) AND c.alive = 1;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->execute();
  $hasCourses = $statement->rowCount() !== 0;
  $courses = array();

  if($hasCourses){
    $courses = $statement->fetchAll();
  }

  $sql = "SELECT * FROM courses WHERE teacherId = :participantId
  AND NOW() < ADDDATE(endDate, INTERVAL 1 MONTH)
  AND NOW() > SUBDATE(startDate, INTERVAL 1 MONTH) AND alive = 1";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);

  $statement->execute();

  $teachesCourses = $statement->rowCount() != 0;

  if($teachesCourses){
    $coursesTaught = $statement->fetchAll();
  }

  $sql = "SELECT r.name as name FROM roles r INNER JOIN participantRoles pr
  ON r.roleId = pr.roleId WHERE pr.participantId = :participantId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->execute();

  $resultsRoles = $statement->fetchAll();

  function getTranslatedNameString($elem) {
    global $participant;
    return translateRoleName($elem['name'], $participant['gender']);
  }
  $roleNames = array_map('getTranslatedNameString', $resultsRoles); //getNameString defined in common.

  
} catch(PDOException $error) {
  handleError($error);
  die();
}

?>

<main>
<div class="heading">
<h1><?php echo escape($participant['firstName'] . " " . $participant['lastName']); ?></h1>
</div>
<?php if (hasAdminPermission()) { ?>
  <form method="post" action="admin/editParticipant.php?participantId=<?php echo escape($participantId);?>">
    <input type=submit id="editParticipant" class="orange-submit edit-button" value="editar participante">
  </form>
  <?php } ?>
  <div id="participantInfo">
    <?php
    $imageFile = $participant['imageLocation'];
    if(file_exists($imageFile)) { ?>
    <img id="profilePic" src="<?php echo escape($imageFile) ?>">
    <?php } ?>
    <ul id="attributesList">
      <li><strong>Apodo: </strong><?php echo escape($participant['nickname']);?></li>
      <li><strong>Papeles: </strong><?php echo escape(implode(", ", $roleNames)); ?></li>
      <li><strong>Genero: </strong><?php echo escape($participant['gender']); ?></li>
      <li><strong>Age: </strong><?php echo escape($participant['age']);?></li>
      <?php if(hasAdminPermission()) { ?>
      <li><strong>Email: </strong><?php echo escape($participant['email']); ?></li>
      <li><strong>Numero de Teléfono: </strong><?php echo escape($participant['phoneNumber']); ?></li>
      <li><strong>Numero de Teléfono 2: </strong><?php echo escape($participant['phoneNumber_2']); ?></li>
      <li><strong>Fecha de Nacimiento: </strong><?php echo escape(date('d/m/Y', strtotime($participant['dob']))); ?></li>
      <?php } ?>
      <li><strong>Idiomas: </strong><?php echo escape($participant['languages']); ?> </li>
      <li><strong>Comunidad de Origen: </strong><?php echo escape($participant['village']); ?></li>
      <?php if(hasAdminPermission()) { ?>
      <li><strong>Comentarios: </strong><?php echo escape($participant['comments']); ?></li>
      <?php } ?>
    </ul>
</div>

<?php if($hasCourses) { ?>
<div class="participantCourses">
  <h3>Cursos Actuales como Participante</h3>
  <ul>
    <?php foreach($courses as $course) { ?>
    <li><a href="/coursePage.php?courseId=<?php echo escape($course['courseId']); ?>"><strong><?php echo escape($course['name']); ?></strong></a></li>
  <?php } ?>
</ul>
</div>
<?php } ?>
<?php if($teachesCourses) { ?>
<div class="participantCourses">
  <h3>Cursos Actuales como Maestr<?php echo getGenderEnding($participant['gender']); ?></h3>
  <ul>
    <?php foreach($coursesTaught as $course) { ?>
      <li><a href="/coursePage.php?courseId=<?php echo escape($course['courseId']); ?>"><strong><?php echo escape($course['name']); ?></strong></a></li>
    <?php } ?>
  </ul>
  </div>
<?php } ?>




</main>

<?php
include "templates/sidebar.php";
include "templates/footer.php"; ?>
