<?php

require "../common.php";

if(!isset($_GET['assignmentId']) || !isset($_GET['courseId'])){
  echo "no hay tarea seleccionada";
  die();
}

$courseId = $_GET['courseId'];
$assignmentId = $_GET['assignmentId'];

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

$assignmentArray = array(
  'assignmentId' => $assignmentId,
  'name' => $_POST['name'],
  'description' => $_POST['description']
);

$sql = makeUpdateQuery('assignmentId', $assignmentArray, 'assignments');

try {

  $connection->beginTransaction();

  $statement = $connection->prepare($sql);
  $statement->execute($assignmentArray);

  $connection->commit();

  header("location: ../teachers/assignments.php?courseId=" . $courseId);
  die();
}

catch (PDOException $error) {

  $connection->rollBack();
  handleError($error);
  die();
}

 ?>
