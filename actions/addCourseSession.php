<?php

require "../common.php";

checkLogIn();

if(isset($_GET['courseId']) && hasPermission($_GET['courseId']) && isset($_POST['submit']) && isset($_POST['date'])) {

  $courseId = $_GET['courseId'];
  $date = $_POST['date'];

  try {

    $connection->beginTransaction();

    $sql = "SELECT alive, sessionId FROM courseSessions WHERE courseId = :courseId
      AND sessionDate = :sessionDate LIMIT 1;";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->bindParam(':sessionDate', $date, PDO::PARAM_STR);

    $statement->execute();

    if($statement->rowCount() === 0) {

      $sql = "INSERT INTO courseSessions (courseId, sessionDate) VALUES
      (:courseId, :sessionDate);";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->bindParam(':sessionDate', $date, PDO::PARAM_STR);

      $statement->execute();

      $sessionId = $connection->lastinsertId();

      $sql = "SELECT p.participantId as participantId FROM participants p INNER JOIN participantCourses pc
      ON p.participantId = pc.participantId WHERE pc.courseId = :courseId;";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->execute();

      $resultsParticipants = $statement->fetchAll();

      $sql = "INSERT INTO attendance (participantId, sessionId, attended)
      VALUES (:participantId, :sessionId, :attended);";
      $statement = $connection->prepare($sql);
      $attended = 'absent';
      $statement->bindParam(":attended", $attended, PDO::PARAM_STR);

      foreach ($resultsParticipants as $participant) {
        $statement->bindParam(":participantId", $participant['participantId'], PDO::PARAM_INT);
        $statement->bindParam(":sessionId", $sessionId, PDO::PARAM_INT);
        $statement->execute();
      }

    } else {

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      $alive = $result['alive'];
      if($result['alive']) {
        echo "esa sesión ya existe";
        die();
      }
      else {
        $sessionId = $result['sessionId'];
        $sql = "UPDATE courseSessions SET alive = 1 WHERE sessionId = :sessionId;";
        $statement =$connection->prepare($sql);
        $statement->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);

        $statement->execute();
      }
    }

    //check to see if we need to change start and end dates
    $sql = "SELECT startDate, endDate FROM courses WHERE courseId = :courseId LIMIT 1";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    $dates = $statement->fetch(PDO::FETCH_ASSOC);

    if($date < $dates['startDate']) {
      $sql = "UPDATE courses SET startDate = :startDate WHERE courseId = :courseId";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->bindParam(':startDate', $date, PDO::PARAM_STR);
      $statement->execute();
    } else if ($date > $dates['endDate']) {
      $sql = "UPDATE courses SET endDate = :endDate WHERE courseId = :courseId";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->bindParam(':endDate', $date, PDO::PARAM_STR);
      $statement->execute();
    }

    $connection->commit();

    header("location: /teachers/attendance.php?courseId=" . $courseId);
  } catch (PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();
  }
}


 ?>
