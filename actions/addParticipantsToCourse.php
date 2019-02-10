<?php

require "../common.php";

if(isset($_GET['courseId']) && isset($_POST['submit']) && (hasAdminPermission() || isTechnician())) {

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

      $sql = "INSERT INTO participantCourses (participantId, courseId, enrollDate)
      VALUES (:participantId, :courseId, CURDATE())";
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

    $sql = "INSERT INTO attendance (participantId, sessionId, attended)
    SELECT :participantId, cs.sessionId, 'absent'
    FROM courseSessions cs
    WHERE cs.courseId = :courseId
    AND NOT EXISTS (
      SELECT 1
      FROM attendance a
      WHERE a.sessionId = cs.sessionId
      AND a.participantId = :participantId
      LIMIT 1
    );";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

    foreach($newParticipantIds as $participantId) {
      $statement->bindParam(":participantId", $participantId, PDO::PARAM_INT);
      $statement->execute();
    }

    $sql = "INSERT INTO grades (participantId, assignmentId, grade)
    SELECT :participantId, a.assignmentId, NULL
    FROM assignments a
    WHERE a.courseId = :courseId
    AND NOT EXISTS (
      SELECT 1
      FROM grades g
      WHERE g.assignmentId = a.assignmentId
      AND g.participantId = :participantId
      LIMIT 1
    );";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

    foreach($newParticipantIds as $participantId) {
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();
    }

    $sql = "INSERT INTO participantQuotas (participantId, quotaId, amountPaid)
    SELECT :participantId, q.quotaId, 0
    FROM quotas q
    WHERE q.courseId = :courseId
    AND NOT EXISTS (
      SELECT 1
      FROM participantQuotas pq
      WHERE pq.quotaId = q.quotaId
      AND pq.participantId = :participantId
      LIMIT 1
    );";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

    foreach($newParticipantIds as $participantId) {
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();
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
  echo $invalidPermissionMessage;
  die();
}

 ?>
