<?php

require "../common.php";
require "../data/localVariables.php";
include "../templates/header.php";

checkLogIn();

if(!hasAdminPermission()) {
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

  //switch this out at some point soon
  $sql = "SELECT EXISTS (SELECT 1 FROM users WHERE participantId = :participantId
  LIMIT 1) AS result;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->execute();

  $isUser = ($statement->fetch(PDO::FETCH_ASSOC))['result'];

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
   <label for="dpi">DPI: </label>
   <input type="text" id="dpi" name="dpi" value="<?php echo escape($participant['dpi']); ?>"><br>
   <label for="age">Fecha de Nacimiento: </label>
   <input type="date" id="dob" name="dob" value="<?php echo escape($participant['dob']);?>"><br>
   <label for="email">Email: </label>
   <input type="text" id="email" name="email" value="<?php echo escape($participant['email']);?>"><br>
   <label for="phoneNumber">Numero de Teléfono: </label>
   <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo escape($participant['phoneNumber']);?>"><br>
   <label for="phoneNumber_2">Numero de Teléfono 2: </label>
   <input type="text" id="phoneNumber_2" name="phoneNumber_2" value="<?php echo escape($participant['phoneNumber_2']);?>"><br>
   <label for="village">Comunidad de Origen: </label>
   <select id="village" name="village">
     <option value="">--Elige aldea--</option>
     <?php foreach($towns as $town) { ?>
       <option value="<?php echo escape($town);?>" <?php echo $town === $participant['village']?'selected':'';?>><?php echo escape($town);?></option>
     <?php } ?>
   <select><br>
     <label>Idiomas: </label><br>
     <?php foreach($languages as $language) { ?>
       <label for="language-<?php echo escape($language); ?>"><?php echo escape($language);?>:
         <input type="checkbox" id="language-<?php echo escape($language); ?>" name="language-<?php echo escape($language); ?>" value="<?php echo escape($language); ?>"
         <?php echo (strpos($participant['languages'], $language) !== false)?'checked':''; ?>></label>
     <?php } ?><br>
     <label for="language-other">Otros Idiomas: <input type="text" id="language-other" name="language-other"></label><br>
   <label for="picture">Imagen (esto borrará la imagen anterior): </label>
   <input type="hidden" name="MAX_FILE_SIZE" value="1000000" /><!-- Add max size on php side!! -->
   <input type="file" id="picture" name="picture" accept="image"><br>
   <label for="comments">Comentarios: </label>
   <textarea id="comments" name="comments"><?php echo escape($participant['comments']); ?></textarea>
   <input name="submit" type="submit" value="Actualizar" class="orange-submit">
 </form>
 <?php if($isUser) { ?>
   <a href="/user/updatePassword.php?participantId=<?php echo escape($participantId); ?>"><button class="orange-submit">Cambiar Contraseña</button></a>
 <?php } ?>
</main>
<?php
include "../templates/sidebar.php";
include "../templates/footer.php"; ?>
