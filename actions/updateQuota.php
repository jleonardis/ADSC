<?php

require "../common.php";

if(!isset($_GET['quotaId']) || !isset($_GET['courseId'])){
  echo "no hay tarea seleccionada";
  die();
}

$courseId = $_GET['courseId'];
$quotaId = $_GET['quotaId'];

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

$quotaArray = array(
  'quotaId' => $quotaId,
  'name' => $_POST['name'],
  'description' => $_POST['description'],
  'amount' => $_POST['amount']
);

$sql = makeUpdateQuery('quotaId', $quotaArray, 'quotas');

try {

  $connection->beginTransaction();

  $statement = $connection->prepare($sql);
  $statement->execute($quotaArray);

  $connection->commit();

  header("location: ../teachers/quotas.php?courseId=" . $courseId);
  die();
}

catch (PDOException $error) {

  $connection->rollBack();
  handleError($error);
  die();
}

 ?>
