<?php

require "../common.php";

checkLogIn();

if(isset($_GET['quotaId']) && isset($_GET['courseId']) && hasPermission($_GET['courseId'])) {

  $quotaId = $_GET['quotaId'];

  try {

    $sql = "UPDATE quotas SET alive = 0 WHERE quotaId = :quotaId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':quotaId', $quotaId, PDO::PARAM_INT);

    $statement->execute();

    header("location: /teachers/quotas.php?courseId=" . $_GET['courseId']);
    die();

  } catch (PDOException $error) {

    handleError($error);
    die();

  }

}

 ?>
