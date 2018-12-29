<?php

require "../common.php";

checkLogIn();
if(!hasPermission()) {
  echo $invalidPermissionMessage;
  die();
}

if(isset($_GET) && isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];
}
else {
  echo "no hay curso seleccionado";
  die();
}

if(isset($_POST['submit'])) {

  try {

    $sql = "UPDATE attendance SET attended = :attended WHERE attendanceId = :attendanceId;";
    $statement = $connection->prepare($sql);

    foreach($_POST as $key => $value) {
      $statement->bindParam(':attendanceId', $key, PDO::PARAM_INT);
      $statement->bindParam(':attended', $value, PDO::PARAM_BOOL);
      $statement->execute();
    }

    header("location: /coursePage.php?courseId=" . escape($courseId) . "&attendanceUpdated=1");
    die();

  } catch(PDOException $error) {
    handleError($error);
    die();
  }
}
?>
