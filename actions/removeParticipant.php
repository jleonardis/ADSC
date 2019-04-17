<?php

require "../common.php";

checkLogIn();

if(isset($_GET['participantId']) && hasPermission($_GET['participantId'])) {

  $participantId = $_GET['participantId'];

  try {

    $sql = "UPDATE participants SET alive = 0 WHERE participantId = :participantId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);

    $statement->execute();

    header("location: /participantList.php");
    die();

  } catch (PDOException $error) {

    handleError($error);
    die();

  }

}

 ?>
