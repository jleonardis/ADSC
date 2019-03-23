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

  $sql = "UPDATE grades SET grade = :grade WHERE gradeId = :gradeId;";
  $statement = $connection->prepare($sql);

  foreach($_POST as $key => $value) {
    $grade = $value === '' ? null : $value;
    $statement->bindParam(':gradeId', $key, PDO::PARAM_INT);
    $statement->bindParam(':grade', $grade, PDO::PARAM_STR);
    $statement->execute();
  }

  $connection->commit();
  if(isset($_GET['editRedirect']) && isset($_GET['assignmentId'])) {
    header("location: /teachers/editAssignment.php?courseId=" . $courseId . "&assignmentId=" . $_GET['assignmentId']);
    die();
  }
  else {
    header("location: /coursePage.php?courseId=" . escape($courseId) . "&assignmentUpdated=1");
    die();
  }

} catch(PDOException $error) {
  $connection->rollBack();
  handleError($error);
  die();
}

?>
