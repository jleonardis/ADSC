<?php

require "../common.php";

//change to only work for admins
if(isset($_POST['submit']) && checkPermission()) {

  $new_participant = array(
    'firstName' => $_POST['firstName'],
    'lastName' => $_POST['lastName'],
    'gender' => $_POST['gender'],
  );

  $sql = makeInsertQuery($new_participant, "participants");

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_participant);

    header("location: ../participantList.php?participantAdded=1");
    die();

  } catch(PDOException $error) {

    handleError($error);
    echo $sql;
    die();
    header("location: ../participantList.php?participantAdded=0");
    die();

  }
}

echo "<h1>Falló. Que haces aqui?";
