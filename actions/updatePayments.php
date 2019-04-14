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

  $sql = "UPDATE participantQuotas SET amountPaid = :amountPaid,
    receiptNumber = :receiptNumber, paymentDate = :paymentDate
  WHERE participantQuotaId = :participantQuotaId;";
  $statement = $connection->prepare($sql);

  $updateArray = array();

  foreach($_POST as $key => $value) {
    $nameArray = explode('-', $key);
    $participantQuotaId = $nameArray[1];
    if(!array_key_exists($participantQuotaId, $updateArray)) {
        $updateArray[$participantQuotaId] = array();
    }
    $updateArray[$participantQuotaId][$nameArray[0]] = $value;
  }

  foreach($updateArray as $key => $updateRow) {
    $receiptNumber = $updateRow['receiptNumber'] === '' ? null : $updateRow['receiptNumber'];
    $paymentDate = $updateRow['paymentDate'] === '' ? null : $updateRow['paymentDate'];
    $amountPaid = $updateRow['amountPaid'] === '' ? 0 : $updateRow['amountPaid'];

    $statement->bindParam(':amountPaid', $amountPaid, PDO::PARAM_STR);
    $statement->bindParam(':receiptNumber', $receiptNumber, PDO::PARAM_STR);
    $statement->bindParam(':paymentDate', $paymentDate, PDO::PARAM_STR);
    $statement->bindParam(':participantQuotaId', $key, PDO::PARAM_INT);

    $statement->execute();
  }

  $connection->commit();

  header('location: /admin/quotasSummary.php');
  die();

} catch(PDOException $error) {
  $connection->rollBack();
  handleError($error);
  die();
}

?>
