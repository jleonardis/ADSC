<?php

require "../common.php";

checkLogIn();

if(isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];
}
else {
  echo "no hay curso seleccionado";
  die();
}

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

if(isset($_POST['submit'])) {

  try {

    $connection->beginTransaction();

    $sql = "UPDATE participantQuotas SET amountPaid = :amountPaid WHERE participantQuotaId = :participantQuotaId;";
    $statement = $connection->prepare($sql);

    foreach($_POST as $key => $value) {
      $amountPaid = $value ? $value : null;
      $statement->bindParam(':participantQuotaId', $key, PDO::PARAM_INT);
      $statement->bindParam(':amountPaid', $amountPaid, PDO::PARAM_STR);
      $statement->execute();
    }

    $connection->commit();
    header("location: /coursePage.php?courseId=" . escape($courseId) . "&assignmentUpdated=1");
    die();

  } catch(PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();
  }
}
?>
