<?php

require "../common.php";

if(isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];
}

if(isset($_POST['submit']) and checkPermission()) {

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
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_STR);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_STR);

      $statement->execute();
    }

    header("location: ../coursePage.php?courseId=" . $courseId . "&courseAdded=1");
    die();
  } catch(PDOException $error) {
    handleError($error);
    header("location: ../coursePage.php?courseId=" . $courseId . "&courseAdded=0");
    die();
  }
}

 ?>
