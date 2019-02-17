<?php

require "../common.php";

if(isset($_GET['courseId']) && hasPermission($_GET['courseId'])) {

  $courseId = $_GET['courseId'];

  $newAssignment = array(
    'courseId' => $courseId,
    'name' => $_POST['name'],
    'description' => $_POST['description']
  );

  try {

    $connection->beginTransaction();

    $sql = makeInsertQuery($newAssignment, 'assignments');
    $statement = $connection->prepare($sql);
    $statement->execute($newAssignment);

    $assignmentId = $connection->lastInsertId();

    $sql = "INSERT INTO grades (assignmentId, participantId)
    SELECT DISTINCT :assignmentId, participantId
    FROM currentParticipantCourses_View
    WHERE courseId = :courseId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    $connection->commit();
    header("location: /teachers/assignments.php?courseId=" . $courseId . "&assignmentAdded=1");
    die();

  } catch (PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();

  }
}

?>
