<?php

require "../common.php";

//change to only work for admins
checkLogin();

if(isset($_FILES['picture']) && isset($_FILES['picture']['error'])) {
   if($_FILES['picture']['error'] == 2) {
     echo "Esa foto es muy grande. Vete para atras y intenta con una mas pequeña.";
     die();
   }
}

if(isset($_POST['submit']) && hasPermission()) {

  $new_participant = array(
    'firstName' => $_POST['firstName'],
    'lastName' => $_POST['lastName'],
    'gender' => $_POST['gender'],
  );

  $sql = makeInsertQuery($new_participant, "participants");

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_participant);

    if(isset($_FILES['picture'])) {
      $picture = $_FILES['picture'];
      $fileNameArray = explode('.', $picture['name']); //used in line below
      $participantId = $connection->lastInsertId();
      $location = 'uploads/participantPhotos/participant-' . $participantId . '.' . end($fileNameArray);
      if(move_uploaded_file($picture['tmp_name'], '../' . $location)) {
        $sql = "UPDATE participants SET imageLocation = :location WHERE participantId = :participantId";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':location', $location, PDO::PARAM_STR);
        $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
        $statement->execute();
        echo "arrived";
        die();
      }
    }
    header("location: ../participantRegistration.php?participantAdded=1");
    die();

  } catch(PDOException $error) {

    handleError($error);
    die();
    header("location: ../participantRegistration.php?participantAdded=0");
    die();

  }
}

else {
  header('location: index.php');
  die();
}
