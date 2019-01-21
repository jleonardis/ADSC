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

    $sql = "SELECT participantId FROM participantCourses WHERE courseId = :courseId";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    $statement->execute();

    if($statement->rowCount() != 0) {

      $resultsParticipants = $statement->fetchAll();

      $sql = "INSERT INTO participantQuotas (quotaId, participantId) VALUES (:quotaId, :participantId);";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':quotaId', $quotaId, PDO::PARAM_INT);

      foreach($resultsParticipants as $participant) {
        $statement->bindParam(':participantId', $participant['participantId'], PDO::PARAM_INT);
        $statement->execute();
      }
    }

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
