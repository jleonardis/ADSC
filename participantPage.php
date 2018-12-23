<?php

require "common.php";
checkLogIn();

include "templates/header.php";
if(!hasPermission()) {
  echo "no tienes permiso para usar esta pagina";
  die();
}

if(!isset($_GET['participantId'])) {
  echo "no hay participante seleccionado";
  die();
}

$participantId = $_GET['participantId'];

//get course info
try {

  $sql = "SELECT * FROM participants WHERE participantId= :participantId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->execute();

  $result = $statement->fetchAll();

  if(count($result) == 0) {
    echo "ese participante ya no esta en la base de datos";
    die();
  }

  //participantId is primary key so there must only be one
  $participant = $result[0];

} catch(PDOException $error) {
  handleError($error);
}

?>

<h1><?php echo escape($participant['firstName'] . " " . $participant['lastName']); ?></h1>
<main>
  <div id="participantInfo">
    <?php
    $imageFile = $participant['imageLocation'];
    if(file_exists($imageFile)) { ?>
    <img id="profilePic" src="<?php echo escape($imageFile) ?>">
    <?php } ?>
    <ul id="attributesList">
      <li>Apodo: <?php echo escape($participant['nickName']);?></li>
      <li>Genero: <?php echo escape($participant['gender']); ?></li>
      <li>Age: 20</li>
      <li>Fecha de Nacimiento: <?php echo escape($participant['dob']); ?></li>
      <li>Email <?php echo escape($participant['email']); ?></li>
    </ul>
</div>
<div id="participantCourses">

</main>

<?php include "templates/footer.php"; ?>
