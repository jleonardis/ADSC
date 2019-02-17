<?php

require "../common.php";

checkLogIn();

if(isset($_GET['assignmentId']) && isset($_GET['courseId']) && hasPermission($_GET['courseId'])) {

  $assignmentId = $_GET['assignmentId'];

  try {

    $sql = "UPDATE assignments SET alive = 0 WHERE assignmentId = :assignmentId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);

    $statement->execute();

    header("location: /teachers/assignments.php?courseId=" . $_GET['courseId']);
    die();

  } catch (PDOException $error) {

    handleError($error);
    die();

  }

}

 ?>
