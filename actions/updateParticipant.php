<?php

include "../common.php";

if(isset($_GET['participantId'])) {
  $participantId = $_GET['participantId'];

  if(isset($_POST['submit'])) {

    if(isset($_FILES['picture']) && isset($_FILES['picture']['error'])) {
       if($_FILES['picture']['error'] == 2) {
         echo "Esa foto es muy grande. Ve para atras y intenta con una mas pequeña.";
         die();
       }
    }

    $participantArray = array(
      'participantId' => $participantId,
      'firstName' => $_POST['firstName'],
      'lastName' => $_POST['lastName'],
      'nickname' => $_POST['nickname'],
      'age' => postTernary('age'),
      'dob' => postTernary('dob'),
      'email' => $_POST['email']
    );


    $sql = makeUpdateQuery('participantId', $participantArray, 'participants');

    try {

      $statement = $connection->prepare($sql);
      $statement->execute($participantArray);

      if(isset($_FILES['picture'])) {
        $picture = $_FILES['picture'];
        $fileNameArray = explode('.', $picture['name']); //used in line below
        $location = 'uploads/participantPhotos/participant-' . $participantId . '.' . end($fileNameArray);
        if(move_uploaded_file($picture['tmp_name'], '../' . $location)) {
          $sql = "UPDATE participants SET imageLocation = :location WHERE participantId = :participantId";
          $statement = $connection->prepare($sql);
          $statement->bindParam(':location', $location, PDO::PARAM_STR);
          $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
          $statement->execute();
        }
      }

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
