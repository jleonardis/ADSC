<?php

require "../common.php";

checkLogin();

if(!(isset($_GET['courseId']) && isset($_GET['participantId']))){
  handleError(new Exception());
}

$courseId = $_GET['courseId'];

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

$participantId = $_GET['participantId'];

try {

  $connection->beginTransaction();

  $sql = "UPDATE participantCourses SET dropOutDate = CURDATE() WHERE participantId = :participantId
  AND courseId = :courseId AND dropOutDate IS NULL;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

  $statement->execute();

  $connection->commit();

  header('location: /coursePage.php?courseId=' . $courseId);
} catch(Exception $error) {
  handleError($error);
  $connection->rollBack();
}
