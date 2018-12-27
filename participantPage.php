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

//get participant info
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

<main>
<div class="heading">
<h1><?php echo escape($participant['firstName'] . " " . $participant['lastName']); ?></h1>
</div>
<?php if (isAdministrator()) { ?>
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
      <li><strong>Genero: </strong><?php echo escape($participant['gender']); ?></li>
      <li><strong>Age: </strong>20</li>
      <li><strong>Fecha de Nacimiento: </strong><?php echo escape($participant['dob']); ?></li>
      <li><strong>Idiomas: </strong><?php echo escape($participant['languages']); ?> </li>
      <li><strong>Comunidad de Origen: </strong><?php echo escape($participant['village']); ?></li>
      <li><strong>Email </strong><?php echo escape($participant['email']); ?></li>
    </ul>
</div>

</main>

<?php include "templates/footer.php"; ?>
