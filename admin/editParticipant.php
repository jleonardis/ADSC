<?php

require "../common.php";

include "../templates/header.php";

checkLogIn();

if(!isAdministrator()) {
  echo $invalidPermissionMessage;
  die();
}

if(isset($_GET) && isset($_GET['participantUpdated'])) {
  displayActionStatus('participantUpdated', "Participante actualizado con exito!");
}

if(isset($_GET['participantId'])) {

  $participantId = $_GET['participantId'];

  $sql = "SELECT * FROM participants WHERE participantId = :participantId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() == 0) {
    echo "ese participante ya no está en la base de datos";
    die();
  }

  $result = $statement->fetch(PDO::FETCH_ASSOC);

  $participant = $result;

} else {

  echo "no hay participante seleccionado";
  die();

}
 ?>
 <main>
<h2>Editar Participante: <?php echo escape($participant['firstName'] . ' ' . $participant['lastName']);?></h2>
 <form form class="submit-form" enctype="multipart/form-data" method="post" action="../actions/updateParticipant.php?participantId=<?php echo escape($participantId); ?>">
   <label for="firstName">Nombre: </label>
   <input type="text" name="firstName" id="firstName" value="<?php echo escape($participant['firstName']);?>"><br>
   <label for="lastName">Apellido: </label>
   <input type="text" name="lastName" id="lastName" value="<?php echo escape($participant['lastName']);?>"><br>
   <label for="nickname">Apodo: </label>
   <input type="text" id="nickname" name="nickname" value="<?php echo escape($participant['nickname']);?>"><br>
   <label for="age">Edad: </label>
   <input type="number" id="age" name="age" value="<?php echo escape($participant['age']);?>"><br>
   <label for="dob">Fecha de Nacimiento: </label>
   <input type="date" id="dob" name="dob" value="<?php echo escape($participant['dob']);?>"><br>
   <label for="email">Email: </label>
   <input type="text" id="email" name="email" value="<?php echo escape($participant['email']);?>"><br>
   <label for="picture">Imagen (esto borrará la imagen anterior): </label>
   <input type="hidden" name="MAX_FILE_SIZE" value="1000000" /><!-- Add max size on php side!! -->
   <input type="file" id="picture" name="picture" accept="image"><br>
   <input name="submit" type="submit" value="Actualizar" class="orange-submit">
 </form>
</main>
<?php
include "../templates/sidebar.php";
include "../templates/footer.php"; ?>
