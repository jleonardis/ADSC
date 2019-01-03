<?php

require "../common.php";

if(isset($_GET['courseId']) && isset($_POST['submit']) && hasPermission()) {

  $courseId = $_GET['courseId'];

  $newParticipantIds = array();
  foreach($_POST as $key => $participant) {
    if($participant == "check") {
      array_push($newParticipantIds, $key);
    }
  }
  try {

    $sql = "";
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
    $attended = false;
    $statement->bindParam(":attended", $attended, PDO::PARAM_BOOL);

    foreach($resultsSessions as $session) {
      foreach($newParticipantIds as $participantId) {
      $statement->bindParam(":participantId", $participantId, PDO::PARAM_INT);
      $sessionId = $session['sessionId'];
      $statement->bindParam(":sessionId", $sessionId, PDO::PARAM_INT);
      $statement->execute();
      }
    }

    header("location: ../coursePage.php?courseId=" . $courseId . "&participantsAdded=1");
    die();

  } catch(PDOException $error) {
    handleError($error);
    header("location: ../coursePage.php?courseId=" . $courseId . "&participantsAdded=0");
    die();
  }
}
else {
  header('location: ../index.php');
  die();
}

 ?>
