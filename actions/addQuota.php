<?php

require "../common.php";

if(isset($_GET['courseId']) && isset($_POST['submit']) && hasPermission($_GET['courseId'])) {

  $courseId = $_GET['courseId'];

  $newQuota = array(
    'courseId' => $courseId,
    'name' => $_POST['name'],
    'description' => $_POST['description'],
    'amount' => $_POST['amount']
  );

  try {

    $connection->beginTransaction();

    $sql = makeInsertQuery($newQuota, 'quotas');
    $statement = $connection->prepare($sql);
    $statement->execute($newQuota);

    $quotaId = $connection->lastInsertId();

    $sql = "INSERT INTO participantQuotas (quotaId, participantId)
    SELECT :quotaId, participantId
    FROM currentParticipantCourses_View
    WHERE courseId = :courseId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':quotaId', $quotaId, PDO::PARAM_INT);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    $connection->commit();
    header("location: /teachers/quotas.php?courseId=" . $courseId . "&quotaAdded=1");
    die();

  } catch (PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();

  }
}

?>
