<?php

include "../common.php";

if(isset($_GET['participantId'])) {
  $participantId = $_GET['participantId'];

  if(isset($_POST['submit'])) {

    $participantArray = array(
      'participantId' => $participantId,
      'firstName' => $_POST['firstName'],
      'lastName' => $_POST['lastName'],
      'nickname' => $_POST['nickname'],
      'age' => $_POST['age'],
      'dob' => $_POST['dob'],
      'email' => $_POST['email']
    );

    $sql = makeUpdateQuery('participantId', $participantArray, 'participants');

    try {

      $statement = $connection->prepare($sql);
      $statement->execute($participantArray);

      header("location: ../admin/editParticipant.php?participantId=" . $participantId . "&participantUpdated=1");
      die();
    }
    catch (PDOException $error){
      handleError($error);
      header("location: ../admin/editParticipant.php?participantId=" . $participantId . "&participantUpdated=0");
      die();
    }
  }
}
 ?>
