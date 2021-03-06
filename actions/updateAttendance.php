<?php

require "../common.php";

checkLogIn();

if(isset($_GET) && isset($_GET['courseId'])) {
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

  $sql = "UPDATE attendance SET attended = :attended WHERE attendanceId = :attendanceId;";
  $statement = $connection->prepare($sql);

  foreach($_POST as $key => $value) {
    $statement->bindParam(':attendanceId', $key, PDO::PARAM_INT);
    $statement->bindParam(':attended', $value, PDO::PARAM_STR);
    $statement->execute();
  }

  $connection->commit();
  if(isset($_GET['removeSession']) && isset($_GET['sessionId']) && $_GET['removeSession']) {
    header("location: /actions/removeCourseSession.php?courseId=" . escape($courseId) . "&sessionId=" . escape($_GET['sessionId']));
    die();
  }
  else {
    header("location: /coursePage.php?courseId=" . escape($courseId));
    die();
  }

} catch(PDOException $error) {
  $connection->rollBack();
  handleError($error);
  die();
}

?>
