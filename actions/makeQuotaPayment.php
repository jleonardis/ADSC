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


try {

  $connection->beginTransaction();
  $insertArray = array(
    'participantId' => $_POST['participantId'],
    'quotaId' => $_POST['quotaId'],
    'paymentDate' => $_POST['quotaDate'],
    'amountPaid' => $_POST['amountToPay'],
    'discount' => isset($_POST['discount'])?1:0
  );
  $sql = makeInsertQuery($insertArray, 'participantQuotas');
  $statement = $connection->prepare($sql);
  $statement->execute($insertArray);

  $connection->commit();
  header("location: /teachers/quotas.php?courseId=" . escape($courseId));
  die();

} catch(PDOException $error) {
  $connection->rollBack();
  handleError($error);
  die();
}

?>
