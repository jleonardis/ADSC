<?php

require "../common.php";

if(isset($_GET['courseId']) && isset($_POST['submit']) && hasAdminPermission()) {

  $courseId = $_GET['courseId'];

  $newParticipantIds = array();
  foreach($_POST as $key => $participant) {
    if($participant === "check") {
      array_push($newParticipantIds, $key);
    }
  }
  try {

    $connection->beginTransaction();

    foreach($newParticipantIds as $participantId) {

      $sql = "INSERT INTO participantCourses (participantId, courseId) VALUES (:participantId, :courseId)";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

      $statement->execute();

      $sql = "UPDATE participants SET isActive = 1 WHERE participantId = :participantId";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);

      $statement->execute();

      $sql = "SELECT EXISTS (SELECT 1 FROM participantRoles pr INNER JOIN
      roles r ON pr.roleId = r.roleId WHERE pr.participantId = :participantId
      AND r.name = 'student' LIMIT 1) AS result;";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();

      if(!($statement->fetch(PDO::FETCH_ASSOC))['result']){
        //this participant hasn't been assigned student role. assigned it to them.
        $sql = "INSERT INTO participantRoles (participantId, roleId)
        SELECT :participantId AS participantId, r.roleId FROM roles AS r
        WHERE r.name = 'student';";

        $statement = $connection->prepare($sql);
        $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
        $statement->execute();
      }
    }

    //get course sessions to update attendance table
    $sql = "SELECT sessionId FROM courseSessions WHERE courseId = :courseId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(":courseId", $courseId, PDO::PARAM_INT);
    $statement->execute();

    $resultsSessions = $statement->fetchAll();

    $sql = "INSERT INTO attendance (participantId, sessionId, attended)
    VALUES (:participantId, :sessionId, :attended);";
    $statement = $connection->prepare($sql);
    $attended = 'absent';
    $statement->bindParam(":attended", $attended, PDO::PARAM_STR);

    foreach($resultsSessions as $session) {
      foreach($newParticipantIds as $participantId) {
      $statement->bindParam(":participantId", $participantId, PDO::PARAM_INT);
      $sessionId = $session['sessionId'];
      $statement->bindParam(":sessionId", $sessionId, PDO::PARAM_INT);
      $statement->execute();
      }
    }

    //get course assignments to update grades table
    $sql = "SELECT assignmentId FROM assignments WHERE courseId = :courseId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(":courseId", $courseId, PDO::PARAM_INT);
    $statement->execute();

    if($statement->rowCount() !== 0) {

      $resultsAssignments = $statement->fetchAll();

      $sql = "INSERT INTO grades (assignmentId, participantId) VALUES
      (:assignmentId, :participantId);";

      $statement = $connection->prepare($sql);
      foreach($newParticipantIds as $participantId) {
        $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
        foreach($resultsAssignments as $assignment) {
          $statement->bindParam(':assignmentId', $assignment['assignmentId'], PDO::PARAM_INT);
          $statement->execute();
        }
      }
    }

    $connection->commit();
    header("location: ../coursePage.php?courseId=" . $courseId . "&participantsAdded=1");
    die();

  } catch(PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();
  }
}
else {
  header('location: ../index.php');
  die();
}

 ?>
