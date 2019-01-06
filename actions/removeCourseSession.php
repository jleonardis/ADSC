<?php

require "../common.php";

if(isset($_GET['courseId']) && hasPermission($_GET['courseId']) && isset($_GET['sessionId'])) {
  $sessionId = $_GET['sessionId'];
  $courseId = $_GET['courseId'];

  try {

    $connection->beginTransaction();

    $sql = "UPDATE courseSessions SET alive = 0 WHERE sessionId = :sessionId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);

    $statement->execute();

    $connection->commit();

    header("location: /teachers/attendance.php?courseId=" . $courseId);
    die();

  } catch (PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();
  }


}
